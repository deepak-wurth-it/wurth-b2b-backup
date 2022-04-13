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



namespace Mirasvit\Search\Plugin;

use Magento\Framework\Search\Adapter\Mysql\ResponseFactory;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\SearchEngineInterface;

/**
 * If search query is empty OR minimum query length - return empty results (was whole catalog)
 */
class EmptySearchQueryPlugin
{
//
//    /**
//     * @var ResponseFactory
//     */
//    private $responseFactory;
//
//    /**
//     * @param ResponseFactory $responseFactory
//     */
//    public function __construct(
//        ResponseFactory $responseFactory
//    ) {
//        $this->responseFactory = $responseFactory;
//    }
//
//    /**
//     * @param SearchEngineInterface $subject
//     * @param callable              $proceed
//     * @param RequestInterface      $request
//     *
//     * @return \Magento\Framework\Search\Response\QueryResponse
//     */
//    public function aroundSearch(
//        SearchEngineInterface $subject,
//        callable $proceed,
//        RequestInterface $request
//    ) {
//        if ($request->getName() == 'quick_search_container' &&
//            !$this->hasSearchQuery($request)) {
//            /** @var \Magento\Framework\Search\Response\QueryResponse $response */
//            $response = $proceed($request);
//
//            return $this->responseFactory->create($this->getEmptyResult($response));
//        }
//
//        return $proceed($request);
//    }
//
//    /**
//     * @param RequestInterface $request
//     *
//     * @return boolean
//     */
//    private function hasSearchQuery(RequestInterface $request)
//    {
//        $query = $request->getQuery();
//        if ($query->getType() == QueryInterface::TYPE_BOOL) {
//            return (isset($query->getShould()['search']) && !empty($query->getShould()['search']))
//                || (isset($query->getMust()['search']) && !empty($query->getMust()['search']));
//        }
//    }
//
//    /**
//     * @param \Magento\Framework\Search\Response\QueryResponse $response
//     *
//     * @return array
//     */
//    private function getEmptyResult($response)
//    {
//        $aggregations = [];
//        /** @var \Magento\Framework\Search\Response\Bucket $aggregation */
//        foreach ($response->getAggregations() as $aggregation) {
//            $aggregations[$aggregation->getName()] = [];
//        }
//
//        return [
//            'documents'    => [],
//            'aggregations' => $aggregations,
//            'total'        => 0,
//        ];
//    }
}
