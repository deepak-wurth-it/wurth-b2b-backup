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

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Mirasvit\ReportApi\Api\Config\ColumnInterface;
use Mirasvit\ReportApi\Api\Config\RelationInterface;
use Mirasvit\ReportApi\Api\Config\TableInterface;
use Mirasvit\ReportApi\Api\Config\TypeInterface;
use Mirasvit\ReportApi\Api\RequestInterface;
use Mirasvit\ReportApi\Api\Service\SelectPillInterface;
use Mirasvit\ReportApi\Api\Service\SelectServiceInterface;
use Mirasvit\ReportApi\Config\Entity\Relation;
use Mirasvit\ReportApi\Config\Entity\Table;
use Mirasvit\ReportApi\Config\Schema;
use Mirasvit\ReportApi\Handler\Select;
use Mirasvit\ReportApi\Handler\SelectFactory;

class SelectService implements SelectServiceInterface
{
    const MAX_TABLE_LENGTH                 = 64;
    const MAX_TABLE_LENGTH_WITHOUT_COUNTER = 50;

    /**
     * @var array
     */
    private static $replicatedTables = [];

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var TableService
     */
    private $tableService;

    /**
     * @var Schema
     */
    private $schema;

    /**
     * @var SelectFactory
     */
    private $selectFactory;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var SelectPillInterface[]
     */
    private $pills;

    public function __construct(
        TableService $tableService,
        SelectFactory $selectFactory,
        Schema $schema,
        ObjectManagerInterface $objectManager,
        ResourceConnection $resource,
        TimezoneInterface $timezone,
        array $pills = []
    ) {
        $this->tableService  = $tableService;
        $this->selectFactory = $selectFactory;
        $this->schema        = $schema;
        $this->objectManager = $objectManager;
        $this->resource      = $resource;
        $this->timezone      = $timezone;
        $this->pills         = $pills;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function replicateTable(TableInterface $table, TableInterface $baseTable)
    {
        if ($table->getConnectionName() != $baseTable->getConnectionName()) {
            // without cross-replication some reports produces error "table or view not exist"
            $this->doReplicate($table, $this->resource->getConnection($baseTable->getConnectionName()));
            $this->doReplicate($baseTable, $this->resource->getConnection($table->getConnectionName()));
        } else {
            // need to replicate tables if both not from 'default' database
            $this->doReplicate($table, $this->resource->getConnection());
            $this->doReplicate($baseTable, $this->resource->getConnection());
        }

        return true;
    }

    /**
     * Relation type between two tables
     * sales_order : sales_order_item = N
     * sales_order_item : sales_order = 1
     *
     * @param TableInterface $currentTable
     * @param TableInterface $requiredTable
     *
     * @return string 1 or n
     */
    public function getRelationType(TableInterface $currentTable, TableInterface $requiredTable)
    {
        if ($currentTable === $requiredTable) {
            return RelationInterface::TYPE_ONE;
        }

        $relations = $this->joinWay($currentTable, $requiredTable);

        if (!$relations) {
            throw new \Exception("Table {$currentTable->getName()} not related with {$requiredTable->getName()}");
        }

        $type = RelationInterface::TYPE_ONE;

        $iterationTable = $currentTable;
        foreach ($relations as $relation) {
            $oppositeTable = $relation->getOppositeTable($iterationTable);

            if ($relation->getType($iterationTable) == RelationInterface::TYPE_MANY) {
                $type = RelationInterface::TYPE_MANY;
            }

            $iterationTable = $oppositeTable;
        }

        return $type;
    }

    /**
     * @param TableInterface $currentTable
     * @param TableInterface $requiredTable
     *
     * @return RelationInterface[]
     */
    public function joinWay(TableInterface $currentTable, TableInterface $requiredTable)
    {
        $key = $currentTable->getName() . '-' . $requiredTable->getName();

        if (!isset($this->cache[$key])) {
            $factor = [];

            $ts   = microtime(true);
            $ways = $this->joinWays($currentTable, $requiredTable);
            //            echo round(microtime(true) - $ts, 4) . ' ' . $key . PHP_EOL . PHP_EOL;

            foreach ($ways as $idx => $way) {
                $factor[$idx] = count($way);
            }

            if (count($factor)) {
                $minIdx            = array_search(min($factor), $factor);
                $this->cache[$key] = $ways[$minIdx];
            } else {
                $this->cache[$key] = [];
            }
        }

        return $this->cache[$key];
    }

    /**
     * @param RequestInterface $request
     * @param ColumnInterface  $column
     * @param Select           $select
     *
     * @throws \Exception
     */
    public function applyPills(RequestInterface $request, $column, $select)
    {
        //        $table = $this->schema->getTable($request->getTable());
        //
        //        foreach ($this->pills as $pill) {
        //            if ($pill->isApplicable($request, $column, $table)) {
        //                $pill->take($select, $column, $table, $request);
        //            }
        //        }
    }

    /**
     * @param ColumnInterface  $column
     * @param RequestInterface $request
     * @param TableInterface   $baseTable
     *
     * @return TableInterface
     * @throws \Zend_Db_Exception
     */
    public function createTemporaryTable(ColumnInterface $column, RequestInterface $request, TableInterface $baseTable)
    {
        $select = $this->selectFactory->create();

        $select->setBaseTable($column->getTable())
            ->addFieldToSelect($baseTable->getPkField())
            ->addColumnToSelect($column, $column->getName())
            ->addFieldToGroup($baseTable->getPkField());

        foreach ($this->pills as $pill) {
            if ($pill->isApplicable($request, $column, $baseTable)) {
                $pill->take($select, $column, $baseTable, $request);
            }
        }

        $select->where($baseTable->getPkField()->toDbExpr() . '>0');

        foreach ($request->getFilters() as $filter) {
            $col = $this->schema->getColumn($filter->getColumn());

            $select->addColumnToFilter($col, [
                $filter->getConditionType() => $filter->getValue(),
            ]);
        }

        $tmpTableName = $this->getTmpTableName(
            $baseTable->getName(),
            $column->getTable()->getName(),
            hash('sha256', microtime(true))
        );

        $tmpTable = $this->resource->getConnection()->newTable($tmpTableName);

        $tmpTable->addColumn(
            $baseTable->getPkField()->getName(),
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [
                'nullable' => false,
                'unsigned' => true,
                'identity' => true,
                'primary'  => true,
            ]
        );

        if (in_array($column->getType()->getType(), [
            TypeInterface::TYPE_PERCENT,
            TypeInterface::TYPE_PK,
            TypeInterface::TYPE_FK,
            TypeInterface::TYPE_NUMBER,
            TypeInterface::TYPE_MONEY,
            TypeInterface::TYPE_QTY,
        ])) {
            $tmpTable->addColumn(
                $column->getName() == 'entity_id' ? $column->getName() . '__value' : $column->getName(),
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false]
            );
        } else {
            $tmpTable->addColumn(
                $column->getName(),
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                ['nullable' => true]
            );
        }

        $this->resource->getConnection()
            ->dropTemporaryTable($tmpTableName);

        $this->resource->getConnection()
            ->createTemporaryTable($tmpTable);

        $insertQuery = $this->resource->getConnection()
            ->insertFromSelect($select, $tmpTableName);

//                echo $insertQuery.PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL;

        $this->applyTimeZone($this->resource->getConnection());
        $this->resource->getConnection()->query($insertQuery);
        $this->restoreTimeZone($this->resource->getConnection());

        /** @var TableInterface $table */
        $table = $this->objectManager->create(Table::class, [
            'name'  => $tmpTableName,
            'label' => $tmpTableName,
        ]);

        $clone = clone $column;
        $table->addColumn($clone);
        $this->schema->addTable($table);

        $relation = $this->objectManager->create(Relation::class, [
            'leftTable'  => $table,
            'leftField'  => $table->getField($baseTable->getPkField()->getName()),
            'rightTable' => $baseTable,
            'rightField' => $baseTable->getPkField(),
            'type'       => '11',
        ]);

        $this->schema->addRelation($relation);
        $table->setIsTmp(true);

        return $table;
    }

    /**
     * {@inheritdoc}
     */
    public function applyTimeZone(AdapterInterface $connection)
    {
        $utc    = $connection->fetchOne('SELECT CURRENT_TIMESTAMP');
        $offset = (new \DateTimeZone($this->timezone->getConfigTimezone()))->getOffset(new \DateTime($utc));
        $h      = floor($offset / 3600);
        $m      = floor(($offset - $h * 3600) / 60);
        $offset = sprintf("%02d:%02d", $h, $m);

        if (substr($offset, 0, 1) != "-") {
            $offset = "+" . $offset;
        }

        //echo "SET time_zone = '$offset'";
        $connection->query("SET time_zone = '$offset'");

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function restoreTimeZone(AdapterInterface $connection)
    {
        $connection->query("SET time_zone = '+00:00'");

        return $this;
    }

    /**
     * @param TableInterface $requiredTable
     * @param TableInterface $currentTable
     *
     * @return Select
     */
    public function createSelect(TableInterface $requiredTable, TableInterface $currentTable)
    {
        $relations = $this->joinWay($currentTable, $requiredTable);

        foreach ($relations as $relation) {
            if ($relation->getOppositeTable($currentTable)) {
                $select = $this->selectFactory->create();
                $select->setBaseTable($relation->getOppositeTable($currentTable));

                $fk = $relation->getOppositeField($currentTable->getPkField());
                $select->addFieldToSelect($fk, 'pk');
                $select->addFieldToGroup($fk);
            }
        }

        if (!isset($select)) {
            throw new LocalizedException(__(
                'Selection does not exist for the required table %1, current table %2',
                $requiredTable,
                $currentTable
            ));
        }


        return $select;
    }

    /**
     * @param TableInterface   $baseTable
     * @param ColumnInterface  $column
     * @param RequestInterface $request
     *
     * @return bool
     * @throws \Exception
     */
    public function isAggregationRequired(TableInterface $baseTable, ColumnInterface $column, RequestInterface $request)
    {
        // case 1: relation 1
        if ($this->getRelationType($baseTable, $column->getTable()) == RelationInterface::TYPE_ONE) {
            return false;
        }

        foreach ($request->getDimensions() as $dimension) {
            $dimension = $this->schema->getColumn($dimension);

            // case 2: we group data by this table
            if ($dimension->getTable() === $column->getTable()) {
                return false;
            }

            // case 3: we group data by table that have relation 1
            if ($this->getRelationType($dimension->getTable(), $column->getTable()) == RelationInterface::TYPE_ONE) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param TableInterface   $table
     * @param AdapterInterface $baseConnection
     */
    private function doReplicate(TableInterface $table, AdapterInterface $baseConnection)
    {
        $tableName = $this->resource->getTableName($table->getName());

        if ($baseConnection->isTableExists($tableName)) {
            return;
        }

        $schema = $this->tableService->describeTable($table);

        $temporaryTable = $baseConnection->newTable($tableName);

        foreach ($schema as $column) {
            $type = $column['DATA_TYPE'];
            if ($column['DATA_TYPE'] == 'int') {
                $type = 'integer';
            } elseif ($column['DATA_TYPE'] == 'varchar') {
                $type = 'text';
            } elseif ($column['DATA_TYPE'] == 'tinyint') {
                $type = 'smallint';
            }

            $temporaryTable->setColumn([
                'COLUMN_NAME'      => $column['COLUMN_NAME'],
                'TYPE'             => $type,
                'LENGTH'           => $column['LENGTH'],
                'COLUMN_POSITION'  => $column['COLUMN_POSITION'],
                'PRIMARY'          => $column['PRIMARY'],
                'PRIMARY_POSITION' => $column['PRIMARY_POSITION'],
                'NULLABLE'         => $column['PRIMARY'] ? false : $column['NULLABLE'],
                'COMMENT'          => $column['COLUMN_NAME'],
            ]);
        }

        try {
            $baseConnection->createTemporaryTable($temporaryTable);
            $offset = 1;
            while (true) {
                $connection = $this->resource->getConnection($table->getConnectionName());
                $pkField    = $table->getPkField();
                $select     = $connection->select();
                $select->from($tableName);

                $rows = $connection->fetchAll($select);

                foreach ($rows as $idx => $row) {
                    $rows[$idx] = $row;
                }

                if (count($rows)) {
                    $baseConnection->insertMultiple($tableName, $rows);
                } else {
                    break;
                }

                $offset++;

                if ($offset > 30) {
                    break;
                }
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * @param TableInterface      $currentTable
     * @param TableInterface      $requiredTable
     * @param RelationInterface[] $relations
     * @param TableInterface[]    $tables
     * @param int                 $level
     *
     * @return RelationInterface[][]
     */
    private function joinWays(TableInterface $currentTable, TableInterface $requiredTable, $relations = [], $tables = [], $level = 0)
    {
        if ($level > 5) {
            return [];
        }

        $areNativeTables = $currentTable->isNative() && $requiredTable->isNative();

        $ways = [];

        $tables[] = $currentTable;

        # fast check (direct relation)
        foreach ($this->schema->getRelations() as $relation) {
            if (in_array($relation, $relations)) {
                continue;
            }

            $oppositeTable = $relation->getOppositeTable($currentTable);

            if ($oppositeTable && $oppositeTable->getName() == $requiredTable->getName()) {
                $ways[] = array_merge($relations, [$relation]);
            }
        }

        if (count($ways)) {
            return $ways;
        }

        foreach ($this->schema->getRelations() as $relation) {
            if (in_array($relation, $relations)) {
                continue;
            }

            $oppositeTable = $relation->getOppositeTable($currentTable);

            if (!$oppositeTable) {
                continue;
            }

            // if both tables are native, then relation must be native too
            if ($areNativeTables && $oppositeTable->isNative() === false) {
                continue;
            }

            if (in_array($oppositeTable, $tables)) {
                continue;
            }

            if ($result = $this->joinWays(
                $oppositeTable,
                $requiredTable,
                array_merge($relations, [$relation]),
                array_merge($tables, [$oppositeTable]),
                $level + 1
            )) {
                foreach ($result as $way) {
                    $ways[] = $way;
                }
            }
        }

        return $ways;
    }

    /**
     * Get name for temporary table.
     * If $tmpTableName greater than MySQL limit set for tables - use imprint of the name.
     *
     * @param string $baseTableName
     * @param mixed  $columnTableName
     * @param int    $tmpTableCounter
     *
     * @return string
     */
    private function getTmpTableName($baseTableName, $columnTableName, $tmpTableCounter)
    {
        $tableName = 'tmp_' . $baseTableName . '__' . $columnTableName;

        if (strlen($tableName) > self::MAX_TABLE_LENGTH_WITHOUT_COUNTER) {
            $tableName = substr($tableName, 0, self::MAX_TABLE_LENGTH_WITHOUT_COUNTER);
        }

        $tableName .= '_' . $tmpTableCounter;

        if (strlen($tableName) > self::MAX_TABLE_LENGTH) {
            $tableName = substr($tableName, 0, self::MAX_TABLE_LENGTH);
        }

        return $tableName;
    }
}
