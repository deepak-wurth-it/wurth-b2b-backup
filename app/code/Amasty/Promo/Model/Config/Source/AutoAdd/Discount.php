<?php

namespace Amasty\Promo\Model\Config\Source\AutoAdd;

/**
 * Discount Calculation Options
 */
class Discount implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        $arr = $this->toArray();
        foreach ($arr as $value => $label) {
            $optionArray[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            \Amasty\Promo\Model\Rule::BEFORE_DISCOUNTS => __('Subtotal Before Discounts'),
            \Amasty\Promo\Model\Rule::AFTER_DISCOUNTS => __('Subtotal After Discounts'),
        ];
    }
}
