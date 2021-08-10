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

namespace Mirasvit\Brand\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class TooltipOptions implements ArrayInterface
{
    const TOOLTIP_TITLE = '{title}';
    const TOOLTIP_SMALL_IMAGE = '{small_image}';
    const TOOLTIP_IMAGE = '{image}';
    const TOOLTIP_DESCRIPTION = '{description}';
    const TOOLTIP_SHORT_DESCRIPTION = '{short_description}';


    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::TOOLTIP_TITLE, 'label' => 'Title'],
            ['value' => self::TOOLTIP_SMALL_IMAGE, 'label' => 'Small Image'],
            ['value' => self::TOOLTIP_IMAGE, 'label' => 'Image'],
            ['value' => self::TOOLTIP_DESCRIPTION, 'label' => 'Description'],
            ['value' => self::TOOLTIP_SHORT_DESCRIPTION, 'label' => 'Short Description']
        ];
    }
}
