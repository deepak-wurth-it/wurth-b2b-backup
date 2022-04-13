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

interface AggregatorInterface
{
    const TYPE_NONE = 'none';

    const TYPE_AVERAGE = 'avg';
    const TYPE_SUM     = 'sum';
    const TYPE_COUNT   = 'cnt';
    const TYPE_CONCAT  = 'concat';

    const TYPE_HOUR        = 'hour';
    const TYPE_DAY         = 'day';
    const TYPE_WEEK        = 'week';
    const TYPE_MONTH       = 'month';
    const TYPE_QUARTER     = 'quarter';
    const TYPE_DAY_OF_WEEK = 'day_of_week';
    const TYPE_YEAR        = 'year';


    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @return array
     */
    public function getExpression();
}
