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
 * @package   mirasvit/module-report
 * @version   1.3.112
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Report\Model;

use Magento\Framework\Api\AbstractSimpleObject;
use Mirasvit\Report\Api\Data\ReportInterface;
use Mirasvit\ReportApi\Api\RequestInterface;
use Mirasvit\ReportApi\Processor\ResponseItem;

abstract class AbstractReport extends AbstractSimpleObject implements ReportInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var \Mirasvit\ReportApi\Api\SchemaInterface
     */
    protected $provider;

    /**
     * @var int
     */
    private $version = 2;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->context  = $context;
        $this->provider = $this->context->getProvider();

        parent::__construct([
            self::COLUMNS            => [],
            self::DIMENSIONS         => [],
            self::INTERNAL_COLUMNS   => [],
            self::INTERNAL_FILTERS   => [],
            self::PRIMARY_DIMENSIONS => [],
            self::PRIMARY_FILTERS    => [],
            self::GRID_CONFIG        => new GridConfig(),
            self::CHART_CONFIG       => new ChartConfig(),
        ]);
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        $code = str_replace('Mirasvit\Reports\Reports\\', '', get_class($this));

        return strtolower(str_replace(['\Interceptor', '\\'], ['', '_'], $code));
    }

    /**
     * {@inheritdoc}
     */
    public function getTable()
    {
        return $this->_get(self::TABLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTable($tableName)
    {
        return $this->setData(self::TABLE, $tableName);
    }

    /** STATE */

    /**
     * {@inheritdoc}
     */
    public function getColumns()
    {
        return $this->_get(self::COLUMNS);
    }

    /**
     * {@inheritdoc}
     */
    public function setColumns(array $columns)
    {
        return $this->setData(self::COLUMNS, array_values($columns));
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensions()
    {
        return $this->_get(self::DIMENSIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function setDimensions(array $columns)
    {
        if ($this->version == 1) {
            return $this->setData(self::PRIMARY_DIMENSIONS, array_values($columns));
        }

        return $this->setData(self::DIMENSIONS, array_values($columns));
    }

    /**
     * {@inheritdoc}
     */
    public function getInternalColumns()
    {
        return $this->_get(self::INTERNAL_COLUMNS);
    }

    /**
     * {@inheritdoc}
     */
    public function setInternalColumns(array $columns)
    {
        return $this->setData(self::INTERNAL_COLUMNS, array_values($columns));
    }

    /**
     * {@inheritdoc}
     */
    public function getInternalFilters()
    {
        return $this->_get(self::INTERNAL_FILTERS);
    }

    /**
     * {@inheritdoc}
     */
    public function setInternalFilters(array $filters)
    {
        return $this->setData(self::INTERNAL_FILTERS, array_values($filters));
    }

    /** SCHEMA */

    /**
     * {@inheritdoc}
     */
    public function getPrimaryDimensions()
    {
        return $this->_get(self::PRIMARY_DIMENSIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function setPrimaryDimensions(array $columns)
    {
        return $this->setData(self::PRIMARY_DIMENSIONS, array_values($columns));
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return $this->_get(self::FILTERS);
    }

    /**
     * {@inheritdoc}
     */
    public function setFilters(array $filters)
    {
        return $this->setData(self::FILTERS, array_values($filters));
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryFilters()
    {
        return $this->_get(self::PRIMARY_FILTERS);
    }

    /**
     * {@inheritdoc}
     */
    public function setPrimaryFilters(array $columns)
    {
        return $this->setData(self::PRIMARY_FILTERS, $columns);
    }
    //
    //    /**
    //     * {@inheritdoc}
    //     */
    //    public function getDefaultColumns()
    //    {
    //        return $this->_get(self::DEFAULT_COLUMNS);
    //    }
    //
    //
    //    /**
    //     * {@inheritdoc}
    //     */
    //    public function setDefaultColumns($columnNames)
    //    {
    //        $this->addColumns($columnNames);
    //
    //        return $this->setData(self::DEFAULT_COLUMNS, $columnNames);
    //    }
    //
    //
    //
    //
    //    private function getColumnsToSelect(ColumnInterface $dimension)
    //    {
    //        $columns = [];
    //
    //        if ($dimension->isUnique()) {
    //            $columns = array_merge_recursive($columns, $this->getByAggregatorType($dimension->getTable(), 'simple'));
    //
    //            foreach ($this->provider->getRelations() as $relation) {
    //                if ($relation->getOppositeTable($dimension->getTable())
    //                    && in_array($relation->getType($dimension->getTable()), ['11', '1n'])) {
    //                    $columns = array_merge_recursive(
    //                        $columns,
    //                        $this->getByAggregatorType($relation->getOppositeTable($dimension->getTable()), 'simple')
    //                    );
    //                }
    //            }
    //        } else {
    //            $columns = array_merge_recursive($columns, $this->getByAggregatorType($dimension->getTable(), 'complex'));
    //
    //            foreach ($this->provider->getRelations() as $relation) {
    //                if ($relation->getOppositeTable($dimension->getTable())
    //                    && $relation->getType($dimension->getTable()) == '11') {
    //                    $columns = array_merge_recursive(
    //                        $columns,
    //                        $this->getByAggregatorType($relation->getOppositeTable($dimension->getTable()), 'complex')
    //                    );
    //                }
    //            }
    //        }
    //
    //        return $columns;
    //    }
    //
    //    private function getColumnsToFilter(ColumnInterface $dimension)
    //    {
    //        $columns = [];
    //
    //        if ($dimension->isUnique()) {
    //
    //        } else {
    //            $columns = array_merge_recursive($columns, $this->getByAggregatorType($dimension->getTable(), 'simple'));
    //        }
    //
    //        return $columns;
    //    }
    //
    //    private function getByAggregatorType(TableInterface $table, $type)
    //    {
    //        $result = [];
    //        foreach ($table->getColumns() as $column) {
    //            if ($this->getAggregatorType($column->getAggregator()) == $type) {
    //                if ($column->getLabel()
    //                    && !$column->isInternal()
    //                    && $column->getAggregator()->getType() != AggregatorInterface::TYPE_CONCAT) {
    //                    $result[] = $column->getIdentifier();
    //                }
    //            }
    //        }
    //
    //        return $result;
    //    }
    //
    //    private function getAggregatorType(AggregatorInterface $aggregator)
    //    {
    //        return in_array($aggregator->getType(), [
    //            AggregatorInterface::TYPE_SUM,
    //            AggregatorInterface::TYPE_COUNT,
    //            AggregatorInterface::TYPE_AVERAGE,
    //            AggregatorInterface::TYPE_CONCAT,
    //        ]) ? 'complex' : 'simple';
    //    }
    //
    //    /**
    //     * {@inheritdoc}
    //     */
    //    public function addAvailableFilters($columnNames)
    //    {
    //        return $this->addData(self::AVAILABLE_FILTERS, array_values($columnNames));
    //    }
    //
    //    /**
    //     * {@inheritdoc}
    //     */
    //    public function getAvailableFilters()
    //    {
    //        return $this->_get(self::AVAILABLE_FILTERS);
    //    }
    //

    /**
     * @return \Mirasvit\Report\Model\GridConfig
     */
    public function getGridConfig()
    {
        return $this->_get(self::GRID_CONFIG);
    }

    /**
     * @return \Mirasvit\Report\Model\ChartConfig
     */
    public function getChartConfig()
    {
        return $this->_get(self::CHART_CONFIG);
    }

    //    /**
    //     * @param string       $key
    //     * @param string|array $data
    //     * @return $this
    //     */
    //    public function addData($key, $data)
    //    {
    //        return $this->setData($key, array_unique(array_merge_recursive(
    //            $this->_get($key),
    //            $data
    //        )));
    //    }

    /**
     * @param ResponseItem     $item
     * @param RequestInterface $request
     *
     * @return array
     */
    public function getActions(ResponseItem $item, RequestInterface $request)
    {
        return [];
    }

    /**
     * @param string $report
     * @param array  $filters
     *
     * @return string
     */
    public function getReportUrl($report, $filters = [])
    {
        return $this->context->urlManager->getUrl(
            'reports/report/view',
            [
                'report' => $report,
                '_query' => [
                    'filters' => $filters,
                ],
            ]
        );
    }

    /**
     * @param array $columns
     *
     * @return $this
     * @deprecated
     */
    public function addFastFilters($columns)
    {
        $this->version = 1;

        $columns = array_merge($this->getPrimaryFilters(), $columns);
        $columns = array_unique($columns);

        return $this->setPrimaryFilters($columns);
    }

    /**
     * @param mixed $columns
     *
     * @return $this
     * @deprecated
     */
    public function addColumns($columns)
    {
        $this->version = 1;

        return $this;
    }

    /**
     * @param array $columns
     *
     * @return $this
     * @deprecated
     */
    public function addDimensions($columns)
    {
        $this->version = 1;

        $columns = array_merge($this->getPrimaryDimensions(), $columns);
        $columns = array_unique($columns);

        return $this->setPrimaryDimensions($columns);
    }


    /**
     * @param array $columns
     *
     * @return $this
     * @deprecated
     */
    public function setDefaultColumns($columns)
    {
        $this->version = 1;

        return $this->setColumns($columns);
    }

    /**
     * @param string $column
     *
     * @return $this
     * @deprecated
     */
    public function setDefaultDimension($column)
    {
        $this->version = 1;

        return $this->setData(self::DIMENSIONS, array_values([$column]));
    }

    /**
     * @param array $filters
     *
     * @return $this
     * @deprecated
     */
    public function setDefaultFilters($filters)
    {
        return $this->setInternalFilters($filters);
    }

    /**
     * @param array $columns
     *
     * @return $this
     * @deprecated
     */
    public function setRequiredColumns($columns)
    {
        return $this->setInternalColumns($columns);
    }

    /**
     * @param array $columns
     *
     * @return $this
     * @deprecated
     */
    public function addDefaultColumns($columns)
    {
        $this->version = 1;

        return $this->setColumns($columns);
    }

    /**
     * @param array $columns
     *
     * @return $this
     * @deprecated
     */
    public function addAvailableFilters($columns)
    {
        return $this;
    }

    /**
     * @return array
     * @deprecated
     */
    public function getAllColumns()
    {
        return [];
    }
}
