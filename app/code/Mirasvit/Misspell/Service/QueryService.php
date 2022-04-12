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
 * @package   mirasvit/module-search-ultimate
 * @version   2.0.56
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



declare(strict_types=1);

namespace Mirasvit\Misspell\Service;

use Magento\Framework\App\Helper\Context;
use Magento\Search\Model\QueryFactory;
use Magento\Framework\UrlFactory;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory as QueryCollectionFactory;
use Magento\Search\Model\SearchEngine;
use Magento\CatalogSearch\Model\Advanced\Request\BuilderFactory as RequestBuilderFactory;
use Magento\Framework\App\ScopeResolverInterface;

class QueryService
{
    protected $fallbackResult = [];

    protected $fallbackCombination = [];

    protected $request;

    protected $query;

    protected $textService;

    protected $urlFactory;

    private $queryCollectionFactory;

    private $searchEngine;

    private $requestBuilderFactory;

    private $scopeResolver;

    public function __construct(
        Context $context,
        QueryFactory $queryFactory,
        UrlFactory $urlFactory,
        TextService $textService,
        QueryCollectionFactory $queryCollectionFactory,
        SearchEngine $searchEngine,
        RequestBuilderFactory $requestBuilderFactory,
        ScopeResolverInterface $scopeResolver
    ) {
        $this->request                = $context->getRequest();
        $this->query                  = $queryFactory->get();
        $this->textService            = $textService;
        $this->urlFactory             = $urlFactory;
        $this->queryCollectionFactory = $queryCollectionFactory;
        $this->searchEngine           = $searchEngine;
        $this->requestBuilderFactory  = $requestBuilderFactory;
        $this->scopeResolver          = $scopeResolver;
    }

    public function getQueryText(): string
    {
        return strip_tags($this->query->getQueryText());
    }

    public function getMisspellText(): string
    {
        return strip_tags((string) $this->request->getParam('o'));
    }

    public function getFallbackText(): string
    {
        return strip_tags((string) $this->request->getParam('f'));
    }

    public function getOriginalQuery(): string
    {
        $originalQuery = strip_tags((string) $this->request->getParam('_q'));

        if (empty($originalQuery)) {
            $originalQuery = $this->getQueryText();
        }

        return $originalQuery;
    }

    public function getMisspellUrl(string $from, string $to): string
    {
        return $this->urlFactory->create()
            ->addQueryParams(['q' => $to, 'o' => $from, '_q' => $this->getOriginalQuery() ])
            ->getUrl('*/*/*');
    }

    public function getFallbackUrl(string $from, string $to): string 
    {
        return $this->urlFactory->create()
            ->addQueryParams(['q' => $to, 'f' => $from, '_q' => $this->getOriginalQuery() ])
            ->getUrl('*/*/*');
    }

    public function fallback(string $query): ?string
    {
        $arQuery = $this->textService->splitWords($query);
        $arQuery = array_slice($arQuery, 0, 5);
        $combinations = $this->getFallbackCombinations($arQuery);

        foreach ($combinations as $combination) {
            $cntResults = $this->getNumResults($combination);
            if ($cntResults > 0) {
                $replace = array_diff($arQuery, explode(' ',$combination));
                return str_replace($replace, '', $query);
            }
        }

        return null;
    }

    private function getFallbackCombinations(array $array): array
    {
        $results = [[]];

        foreach ($array as $element) {
            foreach ($results as $combination) {
                array_push($results, array_merge(array($element), $combination));
            }
        }

        $results = array_map(function($item){return implode(' ', $item);}, $results);
        usort($results,function ($a,$b){return strlen($b)-strlen($a);});
        array_shift($results);
        $results = array_filter($results);

        return $results;
    }

    public function getNumResults(string $query = null): int
    {
        if ($query === null) {
            if ($this->query->getNumResults() === null) {
                return $this->getActualNumResults($this->query->getQueryText());
            }

            return (int) $this->query->getNumResults();
        }

        $collection = $this->queryCollectionFactory->create();

        foreach (explode(' ', trim($query)) as $term) {
            $collection->addFieldToFilter('query_text', ['like' => '%'. $term . '%']);
        }

        $collection->getSelect()->where('LENGTH(query_text) = '. strlen($query));
        $query = $collection->getFirstItem();

        if ($query->getId()) {
            return (int) $query->getNumResults();
        }

        return 1;
    }

    private function getActualNumResults(string $searchTerm): int
    {
        $requestBuilder = $this->requestBuilderFactory->create()
            ->bind('search_term', $searchTerm)
            ->bindDimension('scope', $this->scopeResolver->getScope())
            ->setRequestName('catalogsearch_fulltext');

        $results = $this->searchEngine->search($requestBuilder->create());
        return $results->getTotal();
    }
}
