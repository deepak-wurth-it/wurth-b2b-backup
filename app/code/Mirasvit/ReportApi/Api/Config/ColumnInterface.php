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

use Mirasvit\Report\Api\Data\ReportInterface;

interface ColumnInterface
{
    const IDENTIFIER = 'identifier';
    const NAME       = 'name';
    const LABEL      = 'label';
    const TABLE      = 'table';
    const TYPE       = 'type';
    const AGGREGATOR = 'aggregator';

    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @return TableInterface
     */
    public function getTable();

    /**
     * @return TypeInterface
     */
    public function getType();

    /**
     * @return AggregatorInterface
     */
    public function getAggregator();

    /**
     * @return bool
     */
    public function isUnique();

    /**
     * @return bool
     */
    public function isInternal();

    /**
     * @return \Zend_Db_Expr
     */
    public function toDbExpr();

    /**
     * @return FieldInterface[]
     */
    public function getFields();

    /**
     * @param SelectInterface $select
     * @return bool
     */
    public function join(SelectInterface $select);

    /**
     * @param SelectInterface $select
     * @return bool
     */
    public function joinRight(SelectInterface $select);

    /**
     * Determine whether the column can be as a filter only or it can be rendered in a report also.
     * @param ReportInterface $report
     * @return bool
     */
    public function isFilterOnly(ReportInterface $report);
}
