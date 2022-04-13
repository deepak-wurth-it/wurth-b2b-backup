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
 * @package   mirasvit/module-report-api
 * @version   1.0.49
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportApi\Service;

use Mirasvit\ReportApi\Api\Config\AggregatorInterface;
use Mirasvit\ReportApi\Api\Config\ColumnInterface;
use Mirasvit\ReportApi\Api\Config\RelationInterface;
use Mirasvit\ReportApi\Api\Config\TableInterface;
use Mirasvit\ReportApi\Api\Config\TypeInterface;
use Mirasvit\ReportApi\Api\SchemaInterface;
use Mirasvit\ReportApi\Api\Service\ColumnServiceInterface;

class ColumnService implements ColumnServiceInterface
{
    /**
     * @var SchemaInterface
     */
    private $schema;

    /**
     * @var SelectService
     */
    private $selectService;

    /**
     * ColumnService constructor.
     *
     * @param SchemaInterface $schema
     * @param SelectService   $selectService
     */
    public function __construct(
        SchemaInterface $schema,
        SelectService $selectService
    ) {
        $this->schema        = $schema;
        $this->selectService = $selectService;
    }

    /**
     * @param array $dimensions
     *
     * @return array
     */
    public function getApplicableDimensions(array $dimensions)
    {
        $result = [];

        foreach ($this->schema->getTables() as $table) {
            try {
                foreach ($table->getColumns() as $column) {
                    if (!$this->isAggregator($column->getAggregator())
                        && in_array($column->getType()->getType(), ['select', 'str', 'date', 'store', 'pk', 'fk', 'country'])) {
                        $result[$column->getIdentifier()] = $column->getIdentifier();
                    }
                }
            } catch (\Exception $e) {
            }
        }

        return array_values($result);
    }

    /**
     * @param array $dimensions
     *
     * @return array
     */
    public function getApplicableColumns(array $dimensions)
    {
        $result = [];

        $dim = isset($dimensions[0]) ? $this->schema->getColumn($dimensions[0]) : null;

        foreach ($this->schema->getTables() as $table) {
            if (!$table->isNative()) {
                continue;
            }

            if (!$dim) {
                $dim = $this->schema->getColumn($table->getPkField()->getIdentifier());
            }

            foreach ($table->getColumns() as $column) {
                if ($column->isInternal()) {
                    continue;
                }

                if ($this->isAllowedAsColumn($column, $table, $dim)) {
                    $result[$column->getIdentifier()] = $column->getIdentifier();
                }
            }
        }

        return array_values($result);
    }

    /**
     * @param ColumnInterface $column
     * @param TableInterface  $table
     * @param ColumnInterface $dim
     *
     * @return bool
     */
    private function isAllowedAsColumn(ColumnInterface $column, TableInterface $table, ColumnInterface $dim)
    {
        $cTable = $column->getTable();
        $dTable = $dim->getTable();

        try {
            $relColumnTable = $this->selectService->getRelationType($table, $cTable);
            $relDimTable    = $this->selectService->getRelationType($table, $dTable);
        } catch (\Exception $e) {
            return false;
        }

        if ($relDimTable == RelationInterface::TYPE_ONE) {
            if ($relColumnTable == RelationInterface::TYPE_ONE) {
                if ($this->isPrimaryColumn($dim) && !$this->isAggregatedColumn($column)) {
                    return true;
                }

                if (!$this->isPrimaryColumn($dim) && $this->isAggregatedColumn($column)) {
                    return true;
                }
            } else {
                if ($this->isAggregatedColumn($column)) {
                    return true;
                }
            }
        } else {
            if ($this->isAggregatedColumn($column)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param AggregatorInterface $aggregator
     *
     * @return bool
     */
    private function isAggregator(AggregatorInterface $aggregator)
    {
        return in_array($aggregator->getType(), [
            AggregatorInterface::TYPE_SUM,
            AggregatorInterface::TYPE_AVERAGE,
            AggregatorInterface::TYPE_COUNT,
            AggregatorInterface::TYPE_CONCAT,
        ]);
    }

    /**
     * @param ColumnInterface $column
     *
     * @return bool
     */
    private function isPrimaryColumn(ColumnInterface $column)
    {
        if ($this->isAggregatedColumn($column)) {
            return false;
        }

        if ($column->getType()->getType() == TypeInterface::TYPE_PK) {
            return true;
        }

        if ($column->getIdentifier() === 'sales_order|increment_id') {
            return true;
        }

        if ($column->getIdentifier() === 'catalog_product_entity|sku') {
            return true;
        }

        return false;
    }

    /**
     * @param ColumnInterface $column
     *
     * @return bool
     */
    private function isAggregatedColumn(ColumnInterface $column)
    {
        return $this->isAggregator($column->getAggregator());
    }
}
