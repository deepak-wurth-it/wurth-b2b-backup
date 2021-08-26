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

class ProductPageBrandLogoDescription implements ArrayInterface
{
    const BRAND_LOGO_DESCRIPTION_DISABLED = 0;
    const BRAND_LOGO_SHORT_DESCRIPTION = 1;
    const BRAND_LOGO_DESCRIPTION = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => self::BRAND_LOGO_DESCRIPTION_DISABLED, 'label' => __('Disabled')],
            ['value' => self::BRAND_LOGO_SHORT_DESCRIPTION, 'label' => __('Short Description')],
            ['value' => self::BRAND_LOGO_DESCRIPTION, 'label' => __('Description')],
        ];

        return $options;
    }
}
