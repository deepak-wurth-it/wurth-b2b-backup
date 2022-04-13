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

namespace Mirasvit\SearchMysql\SearchAdapter;

use Magento\Elasticsearch\SearchAdapter\ResponseFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Response\QueryResponse;
use Mirasvit\Search\Service\DebugService;

class Adapter implements AdapterInterface
{
    private $resource;

    private $responseFactory;

    private $mapper;

    private $aggregationBuilder;

    public function __construct(
        ResponseFactory $responseFactory,
        Aggregation\Builder $aggregationBuilder,
        Mapper $mapper,
        ResourceConnection $resource
    ) {
        $this->aggregationBuilder = $aggregationBuilder;
        $this->responseFactory    = $responseFactory;
        $this->mapper             = $mapper;
        $this->resource           = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function query(RequestInterface $request): QueryResponse
    {
        $query = $this->mapper->buildQuery($request);
        /* Amasty Parts Finder use sku_filter for its functionality */
        /* delete the second entrie of stock_index for Amasty Parts Finder compatibility */
        /* it affects mysql results, nothing fountd with stock_index allias */
        if (array_key_exists('sku_filter', $query->getPart('from'))) {
            $from = $query->getPart('from');
            unset($from['stock_index']);
            $query->setPart('from', $from);
        }

        /* Set increased length to prevent group_concat_max_len - related issues */
        $this->resource->getConnection()->query('SET SESSION group_concat_max_len = 1000000;');

        $pairs = $this->resource->getConnection()->fetchPairs($query);

        $documents = [];
        foreach ($pairs as $id => $score) {
            $documents[] = [
                '_id'    => $id,
                '_score' => $score,
            ];
        }

        DebugService::log($query->__toString(), 'search_query');
        DebugService::log(\Zend_Json::encode($documents), 'search_results');

        $aggregations = $this->aggregationBuilder->build($request, $documents);

        return $this->responseFactory->create([
            'documents'    => $documents,
            'aggregations' => $aggregations,
            'total'        => count($documents),
        ]);
    }
}
