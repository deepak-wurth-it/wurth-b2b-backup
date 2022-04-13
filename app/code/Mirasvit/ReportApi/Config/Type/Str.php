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

class Str implements TypeInterface
{
    /**
     * @return string
     */
    public function getType()
    {
        return self::TYPE_STR;
    }

    /**
     * @return array|string[]
     */
    public function getAggregators()
    {
        return [AggregatorInterface::TYPE_NONE, AggregatorInterface::TYPE_CONCAT];
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
        return self::FILTER_TYPE_TEXT;
    }

    /**
     * @param number|string $actualValue
     * @param AggregatorInterface $aggregator
     * @return \Magento\Framework\Phrase|number|string
     */
    public function getFormattedValue($actualValue, AggregatorInterface $aggregator)
    {
        if ($actualValue === null) {
            return __('N/A');
        }

        return $actualValue;
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
