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
 * @package   mirasvit/module-navigation
 * @version   2.0.12
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\LayeredNavigation\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class FilterItemDisplayModeSource implements OptionSourceInterface
{
    const OPTION_LINK            = 'link';
    const OPTION_SIMPLE_CHECKBOX = 'simple_checkbox';
    const OPTION_CHECKBOX        = 'checkbox';
    const OPTION_CIRCLE          = 'circle';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::OPTION_LINK, 'label' => __('Default (link)')],
            ['value' => self::OPTION_SIMPLE_CHECKBOX, 'label' => __('Simple Checkbox')],
            ['value' => self::OPTION_CHECKBOX, 'label' => __('Checkbox')],
            ['value' => self::OPTION_CIRCLE, 'label' => __('Circle')],
        ];
    }
}
