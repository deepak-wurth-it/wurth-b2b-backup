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

class Number implements TypeInterface
{
    /**
     * @return string
     */
    public function getType()
    {
        return self::TYPE_NUMBER;
    }

    /**
     * @return array|string[]
     */
    public function getAggregators()
    {
        return ['none', 'sum', 'avg'];
    }

    /**
     * @return string
     */
    public function getValueType()
    {
        return self::VALUE_TYPE_NUMBER;
    }

    /**
     * @return string
     */
    public function getJsType()
    {
        return self::JS_TYPE_NUMBER;
    }

    /**
     * @return string
     */
    public function getJsFilterType()
    {
        return self::FILTER_TYPE_TEXT_RANGE;
    }

    /**
     * @param number|string $actualValue
     * @param AggregatorInterface $aggregator
     * @return false|float|number|string
     */
    public function getFormattedValue($actualValue, AggregatorInterface $aggregator)
    {
        if ($actualValue === null) {
            return self::NA;
        }

        if (ceil($actualValue) == $actualValue) {
            return ceil($actualValue);
        }

        return round($actualValue, 2);
    }

    /**
     * @param number|string $actualValue
     * @param AggregatorInterface $aggregator
     * @return number|string
     */
    public function getPk($actualValue, AggregatorInterface $aggregator)
    {
        return $actualValue;
    }
}
