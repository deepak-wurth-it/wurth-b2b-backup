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

interface RequestInterface
{
    /**
     * @param string $table
     * @return $this
     */
    public function setTable($table);

    /**
     * @return string
     */
    public function getTable();

    /**
     * @param string[] $columns
     * @return $this
     */
    public function setColumns(array $columns);

    /**
     * @return string[]
     */
    public function getColumns();

    /**
     * @param string $column
     * @return $this
     */
    public function addColumn($column);

    /**
     * @param \Mirasvit\ReportApi\Api\Processor\RequestFilterInterface[] $filters
     * @return $this
     */
    public function setFilters(array $filters);

    /**
     * @return \Mirasvit\ReportApi\Api\Processor\RequestFilterInterface[]
     */
    public function getFilters();

    /**
     * @param string       $column
     * @param string|array $value
     * @param string       $condition
     * @param string       $group
     * @return $this
     */
    public function addFilter($column, $value, $condition = 'eq', $group = '');

    /**
     * @param array $columns
     * @return $this
     */
    public function setDimensions($columns);

    /**
     * @return array
     */
    public function getDimensions();

    /**
     * @param \Mirasvit\ReportApi\Api\Processor\RequestSortOrderInterface[] $sortOrders
     * @return $this
     */
    public function setSortOrders(array $sortOrders);

    /**
     * @return \Mirasvit\ReportApi\Api\Processor\RequestSortOrderInterface[]
     */
    public function getSortOrders();

    /**
     * @param string $column
     * @param string $direction
     * @return $this
     */
    public function addSortOrder($column, $direction);

    /**
     * @param int $size
     * @return $this
     */
    public function setPageSize($size);

    /**
     * @return int
     */
    public function getPageSize();

    /**
     * @param int $page
     * @return $this
     */
    public function setCurrentPage($page);

    /**
     * @return int
     */
    public function getCurrentPage();

    /**
     * @return string
     */
    public function getQuery();

    /**
     * @param string $query
     * @return $this
     */
    public function setQuery($query);

    /**
     * @return array
     */
    public function toArray();

    /**
     * @return \Mirasvit\ReportApi\Api\ResponseInterface
     */
    public function process();
}
