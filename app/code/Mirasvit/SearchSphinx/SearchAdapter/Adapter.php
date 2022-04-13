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



namespace Mirasvit\SearchSphinx\SearchAdapter;

use Magento\Elasticsearch\SearchAdapter\ResponseFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Response\QueryResponse;
use Mirasvit\SearchMysql\SearchAdapter\Aggregation\Builder as AggregationBuilder;
use Mirasvit\SearchMysql\SearchAdapter\Mapper as MysqlMapper;

class Adapter implements AdapterInterface
{
    private $responseFactory;

    private $aggregationBuilder;

    private $mapper;

    private $mysqlMapper;

    public function __construct(
        ResponseFactory $responseFactory,
        AggregationBuilder $aggregationBuilder,
        MapperQL $mapper,
        MysqlMapper $mysqlMapper
    ) {
        $this->mapper             = $mapper;
        $this->mysqlMapper        = $mysqlMapper;
        $this->responseFactory    = $responseFactory;
        $this->aggregationBuilder = $aggregationBuilder;
    }

    public function query(RequestInterface $request): QueryResponse
    {
        if ($request->getName() == 'catalog_view_container') {
            return $this->fallbackEngine($request);
        }

        try {
            $pairs = $this->mapper->buildQuery($request);
        } catch (\Exception $e) {
            return $this->fallbackEngine($request);
        }

        $documents = [];
        foreach ($pairs as $id => $score) {
            $documents[] = [
                '_id'    => $id,
                '_score' => $score,
            ];
        }

        $aggregations = $this->aggregationBuilder->build($request, $documents);

        return $this->responseFactory->create([
            'documents'    => $documents,
            'aggregations' => $aggregations,
            'total'        => count($documents),
        ]);
    }

    private function fallbackEngine(RequestInterface $request): QueryResponse
    {
        $objectManager = ObjectManager::getInstance();

        return $objectManager->create(\Mirasvit\SearchMysql\SearchAdapter\Adapter::class)
            ->query($request);
    }
}
