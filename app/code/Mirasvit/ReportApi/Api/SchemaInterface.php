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



namespace Mirasvit\ReportApi\Api;

use Mirasvit\ReportApi\Api\Config\ColumnInterface;
use Mirasvit\ReportApi\Api\Config\RelationInterface;
use Mirasvit\ReportApi\Api\Config\TableInterface;

interface SchemaInterface
{
    /**
     * @param string $type
     * @return string
     */
    public function getType($type);

    /**
     * @param string $aggregator
     * @return string
     */
    public function getAggregator($aggregator);

    /**
     * @return TableInterface[]
     */
    public function getTables();

    /**
     * @param string $tableName
     * @return TableInterface
     */
    public function getTable($tableName);

    /**
     * @param string $tableName
     * @return bool
     */
    public function hasTable($tableName);

    /**
     * @param string $identifier
     * @return ColumnInterface|null
     */
    public function getColumn($identifier);

    /**
     * @param string $table
     * @return string[]
     */
    public function getSimpleColumns($table);

    /**
     * @param string $table
     * @return string[]
     */
    public function getComplexColumns($table);

    /**
     * @return RelationInterface[]
     */
    public function getRelations();

    /**
     * @param mixed $relations RelationInterface[]
     * @return $this
     */
    public function setRelations($relations);

    /**
     * @param TableInterface $table
     * @return $this
     */
    public function addTable(TableInterface $table);

    /**
     * @param RelationInterface $relation
     * @return RelationInterface
     */
    public function addRelation(RelationInterface $relation);

    /**
     * Check whether the column is complex or not.
     * @param ColumnInterface $column
     * @return bool
     */
    public function isComplexColumn(ColumnInterface $column);
}
