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

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Mirasvit\LayeredNavigation\Api\Data\AttributeConfigInterface;

class AttributeDisplayModeSource implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Default'),
                'value' => AttributeConfigInterface::DISPLAY_MODE_LABEL,
            ],
            [
                'label' => __('Ranges'),
                'value' => AttributeConfigInterface::DISPLAY_MODE_RANGE,
            ],
            [
                'label' => __('Dropdown'),
                'value' => AttributeConfigInterface::DISPLAY_MODE_DROPDOWN,
            ],
            [
                'label' => __('Slider'),
                'value' => AttributeConfigInterface::DISPLAY_MODE_SLIDER,
            ],
            [
                'label' => __('From-To'),
                'value' => AttributeConfigInterface::DISPLAY_MODE_FROM_TO,
            ],
            [
                'label' => __('Slider & From-To'),
                'value' => AttributeConfigInterface::DISPLAY_MODE_SLIDER_FROM_TO,
            ],
        ];
    }

    /**
     * @param ProductAttributeInterface $attribute
     *
     * @return array
     */
    public function toOptionArrayByType(ProductAttributeInterface $attribute)
    {
        if ($attribute->getFrontendInput() === 'price'
            || in_array($attribute->getBackendType(), ['decimal'])) {
            return $this->filter([
                AttributeConfigInterface::DISPLAY_MODE_RANGE,
                AttributeConfigInterface::DISPLAY_MODE_SLIDER,
                AttributeConfigInterface::DISPLAY_MODE_FROM_TO,
                AttributeConfigInterface::DISPLAY_MODE_SLIDER_FROM_TO,
            ]);
        } elseif ($attribute->getFrontendInput() === 'select') {
            return $this->filter([
                AttributeConfigInterface::DISPLAY_MODE_LABEL,
            ]);
        } else {
            return $this->filter([
                AttributeConfigInterface::DISPLAY_MODE_LABEL,
            ]);
        }
    }

    private function filter(array $needle)
    {
        $filtered = [];

        foreach ($this->toOptionArray() as $option) {
            if (in_array($option['value'], $needle)) {
                $filtered[] = $option;
            }
        }

        return $filtered;
    }
}
