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



namespace Mirasvit\ReportApi\Config;

use Mirasvit\ReportApi\Api\Config\AggregatorInterface;
use Mirasvit\ReportApi\Api\Config\ColumnInterface;
use Mirasvit\ReportApi\Api\Config\RelationInterface;
use Mirasvit\ReportApi\Api\Config\TableInterface;
use Mirasvit\ReportApi\Api\SchemaInterface;
use Mirasvit\ReportApi\Config\Loader\MapFactory;

class Schema implements SchemaInterface
{
    /**
     * @var MapFactory
     */
    private $mapFactory;

    /**
     * @var TableInterface[]
     */
    private $tablePool = [];

    /**
     * @var RelationInterface[]
     */
    private $relationPool = [];

    /**
     * @var array
     */
    private $typePool = [];

    /**
     * @var array
     */
    private $aggregatorPool;

    /**
     * Schema constructor.
     * @param MapFactory $mapFactory
     * @param array $type
     * @param array $aggregator
     */
    public function __construct(
        MapFactory $mapFactory,
        array $type = [],
        array $aggregator = []
    ) {
        $this->mapFactory     = $mapFactory;
        $this->typePool       = $type;
        $this->aggregatorPool = $aggregator;
    }

    /**
     * @param string $type
     * @return mixed|string
     * @throws \Exception
     */
    public function getType($type)
    {
        //class type
        if (strpos($type, '\\') !== false) {
            return $type;
        }

        //predefined type
        if (isset($this->typePool[$type])) {
            return $this->typePool[$type];
        }

        throw new \Exception("Unsupported type $type");
    }

    /**
     * @param string $aggregator
     * @return mixed|string
     * @throws \Exception
     */
    public function getAggregator($aggregator)
    {
        if (isset($this->aggregatorPool[$aggregator])) {
            return $this->aggregatorPool[$aggregator];
        }

        throw new \Exception("Unsupported aggregator $aggregator");
    }

    /**
     * @param string $tableName
     * @return bool
     */
    public function hasTable($tableName)
    {
        return key_exists($tableName, $this->getTables());
    }

    /**
     * {@inheritdoc}
     */
    public function getTables()
    {
        $this->initialize();

        return $this->tablePool;
    }

    /**
     * @return $this
     */
    private function initialize()
    {
        if (!$this->tablePool) {
            $this->mapFactory->create()
                ->load();
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumn($identifier)
    {
        if (!is_scalar($identifier)) {
            throw new \Exception("Wrong column type");
        }

        if (count(explode('|', $identifier)) == 3) {
            list(, $tableName, $columnName) = explode('|', $identifier);
        } elseif (count(explode('|', $identifier)) == 2) {
            list($tableName, $columnName) = explode('|', $identifier);
        } else {
            throw new \Exception("Wrong column identifier: $identifier");
        }

        $columnName = trim($columnName);

        $column = $this->getTable($tableName)->getColumn("$columnName");

        return $column;
    }

    /**
     * {@inheritdoc}
     */
    public function getTable($tableName)
    {
        $tableName = trim($tableName);
        if (!key_exists($tableName, $this->getTables())) {
            throw new \Exception(__("Table '%1' is not defined.", $tableName));
        }

        $table = $this->getTables()[$tableName];

        return $table;
    }

    /**
     * @param string $table
     * @return array|string[]
     * @throws \Exception
     */
    public function getSimpleColumns($table)
    {
        $result = [];

        foreach ($this->getTable($table)->getColumns() as $column) {
            if (!in_array($column->getAggregator()->getType(), [
                AggregatorInterface::TYPE_SUM,
                AggregatorInterface::TYPE_COUNT,
                AggregatorInterface::TYPE_AVERAGE,
                AggregatorInterface::TYPE_CONCAT,
            ])
            ) {
                $result[] = $column->getIdentifier();
            }
        }

        return $result;
    }

    /**
     * @param string $table
     * @return array|string[]
     * @throws \Exception
     */
    public function getComplexColumns($table)
    {
        $result = [];

        foreach ($this->getTable($table)->getColumns() as $column) {
            if ($this->isComplexColumn($column)) {
                $result[] = $column->getIdentifier();
            }
        }

        return $result;
    }

    /**
     * @param ColumnInterface $column
     * @return bool
     */
    public function isComplexColumn(ColumnInterface $column)
    {
        return in_array($column->getAggregator()->getType(), [
            AggregatorInterface::TYPE_SUM,
            AggregatorInterface::TYPE_COUNT,
            AggregatorInterface::TYPE_AVERAGE,
            AggregatorInterface::TYPE_CONCAT,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getRelations()
    {
        return $this->relationPool;
    }

    /**
     * {@inheritdoc}
     */
    public function setRelations($relations)
    {
        $this->relationPool = $relations;

        return $this;
    }

    /**
     * @param TableInterface $table
     * @return $this|SchemaInterface
     */
    public function addTable(TableInterface $table)
    {
        $this->tablePool[$table->getName()] = $table;

        return $this;
    }

    /**
     * @param RelationInterface $relation
     * @return $this|RelationInterface
     */
    public function addRelation(RelationInterface $relation)
    {
        $this->relationPool[] = $relation;

        return $this;
    }
}
