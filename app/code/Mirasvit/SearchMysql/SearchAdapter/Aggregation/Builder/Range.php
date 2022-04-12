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



namespace Mirasvit\SearchMysql\SearchAdapter\Aggregation\Builder;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\Aggregation\RangeBucket;
use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;

class Range
{
    const GREATER_THAN = '>=';
    const LOWER_THAN   = '<';

    private $metricsBuilder;

    private $resource;

    private $connection;

    public function __construct(
        Metrics $metricsBuilder,
        ResourceConnection $resource
    ) {
        $this->metricsBuilder = $metricsBuilder;
        $this->resource       = $resource;
        $this->connection     = $resource->getConnection();
    }

    public function build(
        \Mirasvit\SearchMysql\SearchAdapter\Aggregation\DataProviderInterface $dataProvider,
        array $dimensions,
        RequestBucketInterface $bucket,
        Table $entityIdsTable
    ): array {
        /** @var RangeBucket $bucket */
        $select  = $dataProvider->getDataSet($bucket, $dimensions, $entityIdsTable);
        $metrics = $this->metricsBuilder->build($bucket);

        /** @var Select $fullQuery */
        $fullQuery = $this->connection->select();
        $fullQuery->from(['main_table' => $select], null);
        $fullQuery = $this->generateCase($fullQuery, $bucket->getRanges());
        $fullQuery->columns($metrics);
        $fullQuery->group(new \Zend_Db_Expr('1'));

        return $dataProvider->execute($fullQuery);
    }

    private function generateCase(Select $select, array $ranges): Select
    {
        $casesResults = [];
        $field        = RequestBucketInterface::FIELD_VALUE;
        foreach ($ranges as $range) {
            $from = $range->getFrom();
            $to   = $range->getTo();
            if ($from && $to) {
                $casesResults = array_merge(
                    $casesResults,
                    ["`{$field}` BETWEEN {$from} AND {$to}" => "'{$from}_{$to}'"]
                );
            } elseif ($from) {
                $casesResults = array_merge($casesResults, ["`{$field}` >= {$from}" => "'{$from}_*'"]);
            } elseif ($to) {
                $casesResults = array_merge($casesResults, ["`{$field}` < {$to}" => "'*_{$to}'"]);
            }
        }
        $cases = $this->connection
            ->getCaseSql('', $casesResults);
        $select->columns([RequestBucketInterface::FIELD_VALUE => $cases]);

        return $select;
    }
}
