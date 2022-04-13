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
use Magento\Framework\ObjectManagerInterface;
use Mirasvit\ReportApi\Api\Config\AggregatorInterface;
use Mirasvit\ReportApi\Api\Config\CollectionInterface;
use Mirasvit\ReportApi\Api\Config\RelationInterface;
use Mirasvit\ReportApi\Api\Config\TypeInterface;
use Mirasvit\ReportApi\Api\RequestInterface;
use Mirasvit\ReportApi\Config\Entity\Column;
use Mirasvit\ReportApi\Config\Entity\Table;
use Mirasvit\ReportApi\Config\Schema;
use Mirasvit\ReportApi\Service\SelectService;

class Collection implements CollectionInterface
{
    /**
     * @var Schema
     */
    protected $schema;

    /**
     * @var Select
     */
    private $select;

    /**
     * @var SelectService
     */
    private $selectService;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var array
     */
    private $items = [];

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var SelectFactory
     */
    private $selectFactory;

    /**
     * Collection constructor.
     *
     * @param SelectFactory          $selectFactory
     * @param SelectService          $selectService
     * @param ResourceConnection     $resource
     * @param ObjectManagerInterface $objectManager
     * @param Schema                 $schema
     */
    public function __construct(
        SelectFactory $selectFactory,
        SelectService $selectService,
        ResourceConnection $resource,
        ObjectManagerInterface $objectManager,
        Schema $schema
    ) {
        $this->resource      = $resource;
        $this->selectService = $selectService;
        $this->selectFactory = $selectFactory;
        $this->select        = $selectFactory->create();
        $this->objectManager = $objectManager;
        $this->schema        = $schema;
    }

    /**
     * @param RequestInterface $request
     *
     * @return $this|CollectionInterface
     * @throws \Zend_Db_Exception
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;

        $baseTable = $this->schema->getTable($request->getTable());

        $this->connection = $this->resource->getConnection($baseTable->getConnectionName());

        $this->select
            ->setBaseTable($baseTable)
            ->limitPage($request->getCurrentPage(), $request->getPageSize());

        foreach ($request->getColumns() as $identifier) {
            $column = $this->schema->getColumn($identifier);

            $this->selectService->applyPills($request, $column, $this->select);

            if (!$this->selectService->isAggregationRequired($baseTable, $column, $request)) {
                $this->select->addColumnToSelect($column);
            } else {
                /** @var Column $clone */
                $clone = clone $column;

                /** @var Table $table */
                $table = $this->selectService->createTemporaryTable($clone, $request, $baseTable);

                $clone->setTable($table);

                if ($clone->getAggregator()->getType() == AggregatorInterface::TYPE_COUNT) {
                    $agg = $this->objectManager->create($this->schema->getAggregator(AggregatorInterface::TYPE_SUM));
                    $clone->setAggregator($agg);
                }

                if ($clone->getType()->getType() == TypeInterface::TYPE_PERCENT) {
                    $agg = $this->objectManager->create($this->schema->getAggregator(AggregatorInterface::TYPE_AVERAGE));
                    $clone->setAggregator($agg);
                }

                $clone->setExpression('%1');
                $clone->setFields([$clone->getName()]);

                $this->select->addColumnToSelect($clone, $column->getIdentifier());
                //                $select = $this->selectFactory->create();
                //                $select->from($clone->getTable()->getName(), [$clone->toDbExpr()]);
                //
                //                foreach ($this->schema->getRelations() as $r) {
                //                    if ($r->getLeftTable() === $clone->getTable() && $r->getRightTable() === $baseTable) {
                //                        $select->where($r->getCondition());
                //                    }
                //                }
                //
                //                $this->select->columns([
                //                    $column->getIdentifier() => new \Zend_Db_Expr('(' . $select . ')'),
                //                ]);
            }
        }

        $filterTables = [];
        foreach ($request->getFilters() as $filter) {
            $column = $this->schema->getColumn($filter->getColumn());

            if (!$this->selectService->isAggregationRequired($baseTable, $column, $request)
                || $this->select->isJoined($column->getTable())) {
                $this->select->addColumnToFilter($column, [
                    $filter->getConditionType() => $filter->getValue(),
                ]);
            } else {
                /** @var Table $table */
                if (isset($filterTables[$column->getIdentifier()])) {
                    $table = $filterTables[$column->getIdentifier()];
                } else {
                    $table = $this->selectService->createTemporaryTable($column, $request, $baseTable);

                    $filterTables[$column->getIdentifier()] = $table;
                }

                $clone = clone $column;
                $clone->setTable($table);

                if ($clone->getAggregator()->getType() == AggregatorInterface::TYPE_COUNT) {
                    $agg = $this->objectManager->create($this->schema->getAggregator(AggregatorInterface::TYPE_SUM));
                    $clone->setAggregator($agg);
                }

                if ($clone->getType()->getType() == TypeInterface::TYPE_PERCENT) {
                    $agg = $this->objectManager->create($this->schema->getAggregator(AggregatorInterface::TYPE_AVERAGE));
                    $clone->setAggregator($agg);
                }

                $clone->setExpression('%1');
                $clone->setFields([$clone->getName()]);

                $this->select->addColumnToFilter($clone, [
                    $filter->getConditionType() => $filter->getValue(),
                ]);
            }
        }

        foreach ($request->getSortOrders() as $sortOrder) {
            $column = $this->schema->getColumn($sortOrder->getColumn());

            if (!$this->selectService->isAggregationRequired($baseTable, $column, $request)) {
                $this->select->addColumnToOrder($column, $sortOrder->getDirection());
            } else {
                /** @var Table $table */
                $table = $this->selectService->createTemporaryTable($column, $request, $baseTable);

                $clone = clone $column;
                $clone->setTable($table);

                if ($clone->getAggregator()->getType() == AggregatorInterface::TYPE_COUNT) {
                    $agg = $this->objectManager->create($this->schema->getAggregator(AggregatorInterface::TYPE_SUM));
                    $clone->setAggregator($agg);
                }
                $clone->setExpression('%1');
                $clone->setFields([$clone->getName()]);

                $this->select->joinLeft(
                    $table->getName(),
                    $table->getPkField()->toDbExpr() . '=' . $baseTable->getPkField()->toDbExpr(),
                    []
                )->order($clone->toDbExpr() . ' ' . $sortOrder->getDirection());
            }
        }

        foreach ($request->getDimensions() as $dimension) {
            $column = $this->schema->getColumn($dimension);

            if (!$this->selectService->isAggregationRequired($baseTable, $column, $request)) {
                $this->select->addColumnToGroup($column);
            } else {
                /** @var Table $table */
                $table = $this->selectService->createTemporaryTable($column, $request, $baseTable);

                $clone = clone $column;
                $clone->setTable($table);
                $clone->setExpression('%1');
                $clone->setFields([$clone->getName()]);

                $this->select->addColumnToGroup($clone);
            }
        }

        return $this;
    }

    /**
     * @return int|void
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        $this->loadData();

        return count($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function loadData()
    {
        $this->selectService->applyTimeZone($this->connection);
        $rows = $this->connection->fetchAll($this->select);
        $this->selectService->restoreTimeZone($this->connection);

        foreach ($rows as $row) {
            $this->items[] = $row;
        }

        return $this;
    }

    /**
     * @return \ArrayIterator|\Traversable
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        $this->loadData();

        return new \ArrayIterator($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->select->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        $this->select = clone $this->select;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        $countSelect = clone $this->select;
        $countSelect->reset(\Zend_Db_Select::ORDER)
            ->reset(\Zend_Db_Select::LIMIT_COUNT)
            ->reset(\Zend_Db_Select::LIMIT_OFFSET)
            ->reset(\Zend_Db_Select::COLUMNS);

        $countSelect->columns();

        $tableName      = $this->resource->getTableName($this->request->getTable());
        
        try {
            $incrementField = $this->connection->getAutoIncrementField($tableName) ?: '*';
        } catch (\Exception $e) {
            $incrementField = '*';
        }

        $innerSelect = $countSelect->__toString();
        $innerSelect = str_replace('*', $incrementField, $innerSelect);

        $select = 'SELECT COUNT(*) FROM (' . $innerSelect . ') as cnt';

        $this->selectService->applyTimeZone($this->connection);
        $result = $this->connection->fetchOne($select);
        $this->selectService->restoreTimeZone($this->connection);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotals()
    {
        $select = clone $this->select;
        $select->reset(\Zend_Db_Select::ORDER)
            ->reset(\Zend_Db_Select::LIMIT_COUNT)
            ->reset(\Zend_Db_Select::LIMIT_OFFSET)
            ->reset(\Zend_Db_Select::GROUP);

        $result = [];

        $this->selectService->applyTimeZone($this->connection);
        $rows = $this->connection->fetchAll($select);
        $this->selectService->restoreTimeZone($this->connection);

        foreach ($rows as $row) {
            foreach ($row as $k => $v) {
                $column = $this->schema->getColumn($k);

                if (!isset($result[$k])) {
                    $result[$k] = null;
                }
                if ($column->getType()->getValueType() === TypeInterface::VALUE_TYPE_NUMBER) {
                    $result[$k] += (float)$v;
                } else {
                    $result[$k] .= ',' . $v;
                }
            }
        }

        $columnNames = array_keys($result);
        foreach ($columnNames as $columnName) {
            if ($columnName == 'pk') {
                continue;
            }

            $column = $this->schema->getColumn($columnName);

            if ($this->selectService->getRelationType(
                    $column->getTable(),
                    $this->schema->getTable($this->request->getTable())
                ) == RelationInterface::TYPE_MANY) {
                $result[$columnName] = null;
                continue;
            }

            if (count($this->request->getDimensions()) > 1 && $column->getTable()->getName() !== $this->request->getTable()) {
                $result[$columnName] = null;
                continue;
            }

            if ($column->getType()->getValueType() === TypeInterface::VALUE_TYPE_STRING
                && $column->getAggregator()->getType() === AggregatorInterface::TYPE_CONCAT
            ) {
                $values = [];
                foreach (explode(',', $result[$columnName]) as $value) {
                    if ($value && !in_array($value, $values, true)) {
                        $values[] = $value;
                    }
                }

                $result[$columnName] = implode(', ', $values);
            } elseif (!in_array($column->getType()->getValueType(), [TypeInterface::VALUE_TYPE_NUMBER])) {
                $result[$columnName] = null;
            } elseif ($column->getAggregator()->getType() == AggregatorInterface::TYPE_AVERAGE) {
                $result[$columnName] /= count($rows);
            } elseif ($column->getType()->getType() == TypeInterface::TYPE_PERCENT) {
                $result[$columnName] /= count($rows);
            } elseif ($column->getType()->getType() == TypeInterface::TYPE_PK && $column->getAggregator()->getType() == AggregatorInterface::TYPE_NONE) {
                $result[$columnName] = null;
            }
        }

        return $result;
    }
}
