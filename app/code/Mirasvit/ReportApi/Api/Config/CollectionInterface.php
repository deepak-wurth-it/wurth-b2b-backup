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



namespace Mirasvit\ReportApi\Api\Config;

use Magento\Framework\DataObject;
use Mirasvit\ReportApi\Api\RequestInterface;

interface CollectionInterface extends \IteratorAggregate, \Countable
{
    /**
     * @param RequestInterface $request
     * @return $this
     */
    public function setRequest(RequestInterface $request);

    //    /**
    //     * @param TableInterface $table
    //     * @return $this
    //     */
    //    public function setBaseTable(TableInterface $table);
    //
    //    /**
    //     * @param ColumnInterface $column
    //     * @return $this
    //     */
    //    public function addColumnToGroup(ColumnInterface $column);
    //
    //    /**
    //     * @param ColumnInterface $column
    //     * @return $this
    //     */
    //    public function addColumnToSelect(ColumnInterface $column);
    //
    //    /**
    //     * @param ColumnInterface $column
    //     * @param array $condition
    //     * @return $this
    //     */
    //    public function addColumnToFilter(ColumnInterface $column, array $condition);
    //
    //    /**
    //     * @param ColumnInterface $column
    //     * @param string $direction
    //     * @return $this
    //     */
    //    public function addColumnToOrder(ColumnInterface $column, $direction);

    /**
     * @return DataObject
     */
    public function getTotals();

    /**
     * @return int
     */
    public function getSize();
}
