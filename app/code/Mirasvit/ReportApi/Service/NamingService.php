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

use Mirasvit\ReportApi\Api\Config\AggregatorInterface;
use Mirasvit\ReportApi\Api\Config\ColumnInterface;
use Mirasvit\ReportApi\Api\Config\TypeInterface;

class NamingService
{
    /**
     * @param ColumnInterface $column
     * @return array|\Magento\Framework\Phrase|string
     */
    public static function getLabel(ColumnInterface $column)
    {
        $label = $column->getLabel();

        if (!$label) {
            return $label;
        }

        if ($column->getType()->getType() == TypeInterface::TYPE_PERCENT) {
            return __($label);
        }

        if (strpos($label, '_') !== false) {
            $label = explode('_', $label);
            $label = array_map('ucfirst', $label);
            $label = implode(' ', $label);
        }

        switch ($column->getIdentifier()) {
            case 'sales_order|entity_id__cnt':
                return __('Orders');
                break;

            case 'review|review_id__cnt':
                return __('Reviews');
                break;
        }

        switch ($column->getAggregator()->getType()) {
            //            case AggregatorInterface::TYPE_AVERAGE:
            //                $label = __('Average %1', __($label)->render());
            //                break;
            //            case AggregatorInterface::TYPE_COUNT:
            //                if (strpos(strtolower($label), 'qty') === false) {
            //                    $label = __("{$label}s")->render();
            //                }
            //                break;
            //            case AggregatorInterface::TYPE_SUM:
            //                if (strpos(strtolower($label), 'total') === false) {
            //                    $label = __('Total %1', __($label)->render());
            //                }
            //                break;
            case AggregatorInterface::TYPE_DAY:
                $label = __('Day');
                break;
            case AggregatorInterface::TYPE_DAY_OF_WEEK:
                $label = __('Day of Week');
                break;
            case AggregatorInterface::TYPE_WEEK:
                $label = __('Week');
                break;
            case AggregatorInterface::TYPE_MONTH:
                $label = __('Month');
                break;
            case AggregatorInterface::TYPE_YEAR:
                $label = __('Year');
                break;
            case AggregatorInterface::TYPE_QUARTER:
                $label = __('Quarter');
                break;
            case AggregatorInterface::TYPE_HOUR:
                $label = __('Hour');
                break;
            //            case AggregatorInterface::TYPE_CONCAT:
            //                $label = __('Group of %1', $label);
            //                break;
        }

        if (!is_object($label)) {
            $label = __($label);
        }

        return $label;
    }
}
