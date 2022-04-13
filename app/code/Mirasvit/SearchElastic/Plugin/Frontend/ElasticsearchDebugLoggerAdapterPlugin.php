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

namespace Mirasvit\SearchElastic\Plugin\Frontend;

use Magento\Elasticsearch7\SearchAdapter\Adapter;
use Magento\Framework\Search\AdapterInterface;
use Magento\Elasticsearch7\SearchAdapter\Mapper;
use Magento\Elasticsearch\SearchAdapter\ResponseFactory;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder as AggregationBuilder;
use Magento\Elasticsearch\SearchAdapter\QueryContainerFactory;
use Psr\Log\LoggerInterface;

use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Response\QueryResponse;
use Mirasvit\Search\Service\DebugService;

/**
 * @see \Magento\Elasticsearch7\SearchAdapter\Adapter::query()
 */
class ElasticsearchDebugLoggerAdapterPlugin extends Adapter implements AdapterInterface
{
    private $mapper;

    private $responseFactory;

    private $connectionManager;

    private $aggregationBuilder;

    private $queryContainerFactory;

    private $logger;

    private $debugService;

    public function __construct(
        Mapper $mapper,
        ResponseFactory $responseFactory,
        ConnectionManager $connectionManager,
        AggregationBuilder $aggregationBuilder,
        QueryContainerFactory $queryContainerFactory,
        LoggerInterface $logger,
        DebugService $debugService
    ) {
        $this->mapper                   = $mapper;
        $this->responseFactory          = $responseFactory;
        $this->connectionManager        = $connectionManager;
        $this->aggregationBuilder       = $aggregationBuilder;
        $this->queryContainerFactory    = $queryContainerFactory;
        $this->logger                   = $logger;
        $this->debugService             = $debugService;
    }

    public function aroundQuery(AdapterInterface $subject, Callable $proceed, RequestInterface $request) : QueryResponse
    {
        if ($this->debugService->isEnabled() && !empty($request->getSort())) {
            $client = $this->connectionManager->getConnection();
            /** @var \Magento\Elasticsearch7\Model\Client\Elasticsearch $client */
            $query = $this->mapper->buildQuery($request);
            DebugService::log(\Zend_Json::encode($query), 'raw_search_query');
            try {
                $rawResponse = $client->query($query);
            } catch (\Exception $e) {
                DebugService::log($e->getMessage(), 'raw_search_response_exception');
                $rawResponse = [];
            }

            DebugService::log(\Zend_Json::encode($rawResponse), 'raw_search_response');
        }

        return $proceed($request);
    }
}
