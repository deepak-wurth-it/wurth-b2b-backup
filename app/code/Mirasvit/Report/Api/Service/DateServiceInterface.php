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



namespace Mirasvit\Report\Api\Service;

interface DateServiceInterface
{
    const TODAY     = 'today';
    const YESTERDAY = 'yesterday';

    const LAST_7_DAYS  = 'last7Days';
    const LAST_30_DAYS = 'last30Days';
    const LAST_90_DAYS = 'last90Days';

    const THIS_WEEK    = 'week';
    const THIS_MONTH   = 'month';
    const THIS_QUARTER = 'quarter';
    const THIS_YEAR    = 'year';

    const PREVIOUS_WEEK    = 'prev_week';
    const PREVIOUS_MONTH   = 'prev_month';
    const PREVIOUS_QUARTER = 'prev_quarter';
    const PREVIOUS_YEAR    = 'prev_year';

    const LIFETIME = 'lifetime';
    const CUSTOM   = 'custom';

    const OFFSET_PERIOD = 'period';
    const OFFSET_WEEK   = 'week';
    const OFFSET_MONTH  = 'month';
    const OFFSET_YEAR   = 'year';

    /**
     * @param bool $addHint
     *
     * @return string[]
     */
    public function getIntervals($addHint = false);

    /**
     * @param string $code
     *
     * @return string
     */
    public function getIntervalHint($code);

    /**
     * @param string $code
     * @param bool   $inStoreTZ
     *
     * @return IntervalInterface
     */
    public function getInterval($code, $inStoreTZ = false);

    /**
     * @param IntervalInterface $interval
     * @param string            $offset
     * @param bool              $inStoreTZ
     *
     * @return IntervalInterface
     */
    public function getPreviousInterval($interval, $offset, $inStoreTZ = false);

    /**
     * @param string $date
     * @param string $format YYYY-MM-DDT hh:mm:ss
     *
     * @return mixed
     */
    public function toZendDate($date, $format = \Zend_Date::ISO_8601);

    /**
     * @param \Zend_Date|string $date
     *
     * @return string
     */
    public function toMysqlDate($date);

    /**
     * @param \Zend_Date|string $from
     * @param \Zend_Date|string $to
     *
     * @return IntervalInterface
     */
    public function toInterval($from, $to);
}
