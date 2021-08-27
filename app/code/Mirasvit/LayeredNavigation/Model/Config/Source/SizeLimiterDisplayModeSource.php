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

class SizeLimiterDisplayModeSource implements OptionSourceInterface
{
    const MODE_DEFAULT   = '';
    const MODE_SHOW_HIDE = 'show-hide';
    const MODE_SCROLL    = 'scroll';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::MODE_DEFAULT,
                'label' => __('Disabled (no limits)'),
            ],
            [
                'value' => self::MODE_SCROLL,
                'label' => __('Scroll box'),
            ],
            [
                'value' => self::MODE_SHOW_HIDE,
                'label' => __('Show/hide link'),
            ],
        ];
    }
}
