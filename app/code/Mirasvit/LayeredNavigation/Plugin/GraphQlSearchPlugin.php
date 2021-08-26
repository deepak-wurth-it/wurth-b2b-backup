<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-navigation
 * @version   2.0.12
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\LayeredNavigation\Plugin;

use Magento\CatalogGraphQl\Model\Resolver\Products\Query\Search;
use Magento\CatalogGraphQl\DataProvider\Product\SearchCriteriaBuilder;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ProductSearch;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\FieldSelection;
use Magento\CatalogGraphQl\Model\Resolver\Products\SearchResult;
use Magento\CatalogGraphQl\Model\Resolver\Products\SearchResultFactory;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Search\Api\SearchInterface;
use Magento\Search\Model\Search\PageSizeProvider;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Implements multi-select for filters (i.e. count without selected filters)
 */

/**
 * @see \Magento\CatalogGraphQl\Model\Resolver\Products\Query\Search::getResult()
 */

class GraphQlSearchPlugin
{
    /**
     * @var SearchInterface
     */
    private $search;

    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * @var PageSizeProvider
     */
    private $pageSizeProvider;

    /**
     * @var FieldSelection
     */
    private $fieldSelection;

    /**
     * @var ProductSearch
     */
    private $productsProvider;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param SearchInterface       $search
     * @param SearchResultFactory   $searchResultFactory
     * @param PageSizeProvider      $pageSize
     * @param FieldSelection        $fieldSelection
     * @param ProductSearch         $productsProvider
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        SearchInterface $search,
        SearchResultFactory $searchResultFactory,
        PageSizeProvider $pageSize,
        FieldSelection $fieldSelection,
        ProductSearch $productsProvider,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->search                = $search;
        $this->searchResultFactory   = $searchResultFactory;
        $this->pageSizeProvider      = $pageSize;
        $this->fieldSelection        = $fieldSelection;
        $this->productsProvider      = $productsProvider;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param array $arguments
     */
    public function aroundGetResult(Search $subject, callable $proceed, ...$arguments): SearchResult
    {
        $args = $arguments[0];
        /* @var $info Magento\Framework\GraphQl\Schema\Type\ResolveInfo */
        $info = $arguments[1];

        if (isset($arguments[3])) {
            $context = $arguments[3];
            return $proceed($args, $info, $context);
        }

        return $this->getResultWithoutContext($args, $info);
    }

    public function getResultWithoutContext(array $args, ResolveInfo $info): SearchResult
    {
        $queryFields    = $this->fieldSelection->getProductsFieldSelection($info);
        $searchCriteria = $this->buildSearchCriteria($args, $info);
        $realPageSize    = $searchCriteria->getPageSize();
        $realCurrentPage = $searchCriteria->getCurrentPage();
        //Because of limitations of sort and pagination on search API we will query all IDS
        $pageSize = $this->pageSizeProvider->getMaxPageSize();
        $searchCriteria->setPageSize($pageSize);
        $searchCriteria->setCurrentPage(0);

        $itemsResults = $this->search->search($searchCriteria);

        $buckets = [];
        foreach ($itemsResults->getAggregations() as $bucketName => $bucket) {
            $buckets[$bucketName] = $bucket;
        }

        foreach ($args['filter'] as $code => $filter) {
            $newArgs = $args;
            unset($newArgs['filter'][$code]);
            $newSearchCriteria = $this->buildSearchCriteria($newArgs, $info);
            $newItemsResults   = $this->search->search($newSearchCriteria);

            /** @var \Magento\Framework\Search\Response\Bucket $bucket */
            foreach ($newItemsResults->getAggregations() as $bucketName => $bucket) {
                if ($bucketName === $code . '_bucket') {
                    $buckets[$bucketName] = $bucket;
                }
            }
        }

        $itemsResults->setAggregations(new \Magento\Framework\Search\Response\Aggregation($buckets));

        //Address limitations of sort and pagination on search API apply original pagination from GQL query
        $searchCriteria->setPageSize($realPageSize);
        $searchCriteria->setCurrentPage($realCurrentPage);
        $searchResults = $this->productsProvider->getList($searchCriteria, $itemsResults, $queryFields);

        $totalPages = $realPageSize ? ((int)ceil($searchResults->getTotalCount() / $realPageSize)) : 0;

        $productArray = [];
        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($searchResults->getItems() as $product) {
            $productArray[$product->getId()]          = $product->getData();
            $productArray[$product->getId()]['model'] = $product;
        }

        return $this->searchResultFactory->create(
            [
                'totalCount'           => $searchResults->getTotalCount(),
                'productsSearchResult' => $productArray,
                'searchAggregation'    => $itemsResults->getAggregations(),
                'pageSize'             => $realPageSize,
                'currentPage'          => $realCurrentPage,
                'totalPages'           => $totalPages,
            ]
        );
    }

    /**
     * @return object
     */
    private function buildSearchCriteria(array $args, ResolveInfo $info)
    {
        $productFields       = (array)$info->getFieldSelection(1);
        $includeAggregations = isset($productFields['filters']) || isset($productFields['aggregations']);
        $searchCriteria      = $this->searchCriteriaBuilder->build($args, $includeAggregations);

        return $searchCriteria;
    }
}
