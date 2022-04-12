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



namespace Mirasvit\ReportApi\Handler;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select\SelectRenderer;
use Mirasvit\ReportApi\Api\Config\ColumnInterface;
use Mirasvit\ReportApi\Api\Config\FieldInterface;
use Mirasvit\ReportApi\Api\Config\RelationInterface;
use Mirasvit\ReportApi\Api\Config\SelectInterface;
use Mirasvit\ReportApi\Api\Config\TableInterface;
use Mirasvit\ReportApi\Config\Schema;
use Mirasvit\ReportApi\Service\SelectService;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Select extends \Magento\Framework\DB\Select implements SelectInterface
{
    /**
     * @var ColumnInterface[]
     */
    private $usedColumnsPool = [];

    /**
     * @var string[]
     */
    private $joinedTablesPool = [];

    /**
     * @var RelationInterface[]
     */
    private $usedRelationsPool = [];

    private $resource;

    private $connection;

    private $baseTable;

    private $schema;

    private $selectService;

    public function __construct(
        ResourceConnection $resource,
        Schema $schema,
        SelectService $selectService,
        SelectRenderer $selectRenderer
    ) {
        $this->schema        = $schema;
        $this->selectService = $selectService;
        $this->resource      = $resource;

        /** @var \Magento\Framework\DB\Adapter\Pdo\Mysql $adapter */
        $adapter = $resource->getConnection();

        parent::__construct($adapter, $selectRenderer);
    }

    /**
     * @param TableInterface $table
     *
     * @return $this
     */
    public function setBaseTable($table)
    {
        $this->baseTable  = $table;
        $this->connection = $this->resource->getConnection($this->baseTable->getConnectionName());

        $this->joinedTablesPool[] = $table->getName();

        $this->from(
            [$table->getName() => $this->resource->getTableName($table->getName())],
            []
        );

        return $this;
    }

    /**
     * @param FieldInterface $field
     * @param null           $alias
     *
     * @return $this
     */
    public function addFieldToSelect(FieldInterface $field, $alias = null)
    {
        $field->join($this);

        $alias = $alias ? $alias : $field->getName();

        if (strrpos($field->getTable()->getName(), 'tmp') === 0 && $alias == 'entity_id') {
            $alias .= '__value';
        }

        $this->columns([
            $alias => $field->toDbExpr(),
        ]);

        return $this;
    }

    /**
     * @param ColumnInterface $column
     * @param string          $alias
     *
     * @return $this
     */
    public function addColumnToSelect(ColumnInterface $column, $alias = null)
    {
        $this->usedColumnsPool[] = $column;

        $column->join($this);

        foreach ($column->getFields() as $field) {
            $field->join($this);
        }

        $alias = $alias ? $alias : $column->getIdentifier();

        if (strrpos($column->getTable()->getName(), 'tmp') === 0 && $alias == 'entity_id') {
            $alias .= '__value';
        }

        $this->columns([
            $alias => $column->toDbExpr(),
        ]);

        return $this;
    }

    /**
     * @param FieldInterface $field
     *
     * @return $this
     */
    public function addFieldToGroup(FieldInterface $field)
    {
        $field->join($this);

        $this->group($field->toDbExpr());

        return $this;
    }

    /**
     * @param ColumnInterface $column
     *
     * @return $this
     */
    public function addColumnToGroup(ColumnInterface $column)
    {
        $column->join($this);

        foreach ($column->getFields() as $field) {
            $field->join($this);
        }

        $this->usedColumnsPool[] = $column;

        $this->group($column->toDbExpr());

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @param ColumnInterface      $column
     * @param integer|string|array $condition
     *
     * @return $this
     */
    public function addColumnToFilter(ColumnInterface $column, $condition)
    {
        //        $this->validateColumn($column);

        $this->usedColumnsPool[] = $column;

        $column->join($this);

        foreach ($column->getFields() as $field) {
            $field->join($this);
        }

        $identifier = $column->getIdentifier();

        // for filters by attributes of type 'multiselect'
        if (strpos($identifier, 'catalog_product_entity') !== false) {
            $attrCode = explode('|', $identifier);
            $attrCode = $attrCode[count($attrCode) - 1];

            $s = $this->getConnection()->select()->from(
                $this->resource->getTableName('eav_attribute')
            )->where(
                'attribute_code = "' . $attrCode . '"'
            )->where(
                'backend_type = "varchar" AND frontend_input = "multiselect"'
            );

            $res = $this->getConnection()->query($s)->fetchAll();

            if (count($res)) {
                $newCond = [];

               foreach ($condition['in'] as $value) {
                   $newCond[] = ['finset' => $value];
               }

               $condition = $newCond;
            }
        }

        $conditionSql = $this->connection->prepareSqlCondition($column->toDbExpr(), $condition);

        if (strpos($conditionSql, 'COUNT(') !== false
            || strpos($conditionSql, 'AVG(') !== false
            || strpos($conditionSql, 'SUM(') !== false
            || strpos($conditionSql, 'CONCAT(') !== false
            || strpos($conditionSql, 'MIN(') !== false
            || strpos($conditionSql, 'MAX(') !== false
        ) {
            $this->having($conditionSql);
        } elseif ($condition) {
            $this->where($conditionSql);
        }


        return $this;
    }

    /**
     * @param ColumnInterface $column
     * @param string          $direction
     *
     * @return $this
     */
    public function addColumnToOrder(ColumnInterface $column, $direction)
    {
        $column->join($this);

        foreach ($column->getFields() as $field) {
            $field->join($this);
        }

        $this->validateColumn($column);
        $this->usedColumnsPool[] = $column;

        $this->order(new \Zend_Db_Expr($column->toDbExpr() . ' ' . $direction));

        return $this;
    }

    /**
     * @param ColumnInterface $column
     *
     * @return bool
     * @throws \Exception
     */
    private function validateColumn(ColumnInterface $column)
    {
        return true;
        if ($this->selectService->getRelationType($this->baseTable, $column->getTable()) !== RelationInterface::TYPE_ONE) {
            throw new \LogicException("Wrong column for select: {$column->getIdentifier()}");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function assemble()
    {
        if (!count($this->_parts[self::FROM])) {
            $this->columns([
                new \Zend_Db_Expr(1),
            ]);
        }

        $query = parent::assemble();

        return $query;
    }

    /**
     * @param TableInterface $table
     *
     * @return bool
     */
    public function joinTable($table)
    {
        if (in_array($table->getName(), $this->joinedTablesPool)) {
            return true;
        }

        $relations = $this->selectService->joinWay($this->baseTable, $table);

        $isJoined = $relations ? true : false;

        /** @var RelationInterface $relation */
        foreach ($relations as $relation) {
            if (!in_array($relation->getRightTable()->getName(), $this->joinedTablesPool)) {
                $isJoined = $this->doJoinTable($relation->getRightTable(), $relation) ? $isJoined : false;
            }

            if (!in_array($relation->getLeftTable()->getName(), $this->joinedTablesPool)) {
                $isJoined = $this->doJoinTable($relation->getLeftTable(), $relation) ? $isJoined : false;
            }
        }


        return $isJoined;
    }

    /**
     * Join $tbl to current select based on relation condition.
     *
     * @param TableInterface    $table
     * @param RelationInterface $relation
     *
     * @return Select
     */
    private function doJoinTable(TableInterface $table, RelationInterface $relation)
    {
        $this->selectService->replicateTable($table, $this->baseTable);

        if ($this->leftJoin(
            [$table->getName() => $table->isTmp()
                ? $table->getName()
                : $this->resource->getTableName($table->getName()),
            ],
            $relation->getCondition(),
            []
        )) {
            $this->usedRelationsPool[] = $relation;
        }

        return $this;
    }

    /**
     * @param array  $name
     * @param string $cond
     * @param string $cols
     *
     * @return bool
     */
    public function leftJoin($name, $cond, $cols = '*')
    {
        if (count($this->joinedTablesPool) > 50) {
            throw new \LogicException("Too many tables for join");
        }

        $n = implode('-', array_merge(array_keys($name), array_values($name)));

        if (!in_array($n, $this->joinedTablesPool)) {
            $this->joinedTablesPool[] = $n;
            $this->joinedTablesPool[] = array_keys($name)[0];

            parent::joinLeft($name, $cond, $cols);

            return true;
        }

        return false;
    }

    /**
     * @param array  $name
     * @param string $cond
     * @param string $cols
     *
     * @return bool
     */
    public function rightJoin($name, $cond, $cols = '*')
    {
        if (count($this->joinedTablesPool) > 50) {
            throw new \LogicException("Too many tables for join");
        }

        $n = implode('-', array_merge(array_keys($name), array_values($name)));

        if (!in_array($n, $this->joinedTablesPool)) {
            $this->joinedTablesPool[] = $n;

            parent::joinRight($name, $cond, $cols);

            return true;
        }

        return false;
    }

    /**
     * @param TableInterface $table
     *
     * @return bool
     */
    public function isJoined(TableInterface $table)
    {
        foreach ($this->joinedTablesPool as $t) {
            if ($t === $table->getName()) {
                return true;
            }
        }

        return false;
    }
}
