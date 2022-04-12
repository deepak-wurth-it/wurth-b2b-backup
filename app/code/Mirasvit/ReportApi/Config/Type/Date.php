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



namespace Mirasvit\ReportApi\Config\Type;

use Mirasvit\ReportApi\Api\Config\AggregatorInterface;
use Mirasvit\ReportApi\Api\Config\TypeInterface;

class Date implements TypeInterface
{
    /**
     * @return string
     */
    public function getType()
    {
        return self::TYPE_DATE;
    }

    /**
     * @return array|string[]
     */
    public function getAggregators()
    {
        return ['none', 'hour', 'day', 'day_of_week', 'month', 'quarter', 'week', 'year'/*, 'dateRange'*/];
    }

    /**
     * @return string
     */
    public function getValueType()
    {
        return self::VALUE_TYPE_STRING;
    }

    /**
     * @return string
     */
    public function getJsType()
    {
        return self::JS_TYPE_TEXT;
    }

    /**
     * @return string
     */
    public function getJsFilterType()
    {
        return self::FILTER_TYPE_DATE_RANGE;
    }

    /**
     * @param number|string $actualValue
     * @param AggregatorInterface $aggregator
     * @return false|\Magento\Framework\Phrase|number|string|null
     */
    public function getFormattedValue($actualValue, AggregatorInterface $aggregator)
    {
        if ($actualValue === null) {
            return null;
        }

        $value = $actualValue;

        switch ($aggregator->getType()) {
            case AggregatorInterface::TYPE_HOUR:
                if (strlen($value) == 1) {
                    $value = '0' . $value;
                }

                $value .= ':00';
                break;

            case AggregatorInterface::TYPE_DAY:
                $value = date('d M, Y', strtotime($actualValue));
                break;

            case AggregatorInterface::TYPE_DAY_OF_WEEK:
                switch ($actualValue) {
                    case 0:
                        $value = __('Monday');
                        break;
                    case 1:
                        $value = __('Tuesday');
                        break;
                    case 2:
                        $value = __('Wednesday');
                        break;
                    case 3:
                        $value = __('Thursday');
                        break;
                    case 4:
                        $value = __('Friday');
                        break;
                    case 5:
                        $value = __('Saturday');
                        break;
                    case 6:
                        $value = __('Sunday');
                        break;
                }
                break;

            case AggregatorInterface::TYPE_WEEK:
                $year = substr($actualValue, 0, 4);
                $week = str_replace($year, '', $actualValue);

                $value = date('d M, Y', strtotime($year . "W" . $week . 1))
                    . ' - '
                    . date('d M, Y', strtotime($year . "W" . $week . 7)) . ' (' . (int)$week . ')';
                break;

            case AggregatorInterface::TYPE_MONTH:
                $value = date('M, Y', strtotime($actualValue));
                break;

            case AggregatorInterface::TYPE_QUARTER:
                $strVal = strtotime($actualValue);
                $year   = date('Y', $strVal);
                switch (date('n', $strVal)) {
                    case 1:
                        $value = 'Jan, ' . $year . ' – Mar, ' . $year;
                        break;
                    case 2:
                        $value = 'Apr, ' . $year . ' – Jun, ' . $year;
                        break;
                    case 3:
                        $value = 'Jul, ' . $year . ' – Sep, ' . $year;
                        break;
                    case 4:
                        $value = 'Oct, ' . $year . ' – Dec, ' . $year;
                        break;
                }
                break;

            case AggregatorInterface::TYPE_YEAR:
                $value = date('Y', strtotime($actualValue));
                break;
            default:
                $value = date('d M, Y H:i', strtotime($actualValue));
        }

        return $value;
    }

    /**
     * @param number|string $actualValue
     * @param AggregatorInterface $aggregator
     * @return false|number|string|null
     */
    public function getPk($actualValue, AggregatorInterface $aggregator)
    {
        if ($actualValue === null) {
            return null;
        }

        $value = $actualValue;

        switch ($aggregator->getType()) {
            case AggregatorInterface::TYPE_DAY:
                $value = date('d', strtotime($actualValue));
                break;
            case AggregatorInterface::TYPE_WEEK:
                $value = date('W', strtotime($actualValue));
                break;
            case AggregatorInterface::TYPE_MONTH:
                $value = date('m', strtotime($actualValue));
                break;
            case AggregatorInterface::TYPE_QUARTER:
                $value = date('n', strtotime($actualValue));
                break;
            case AggregatorInterface::TYPE_YEAR:
                $value = '0';
                break;
        }

        return $value;
    }
}
