<?php

namespace Amasty\Promo\Model\Config\Source\AutoAdd;

/**
 * AutoAdd behavior options
 */
class Mode implements \Magento\Framework\Option\ArrayInterface
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
            \Amasty\Promo\Model\Rule::NOT_AUTO_FREE_ITEMS => __('No'),
            \Amasty\Promo\Model\Rule::AUTO_FREE_ITEMS => __('Yes, free products only'),
            \Amasty\Promo\Model\Rule::AUTO_FREE_DISCOUNTED_ITEMS => __('Yes, discounted and free products'),
        ];
    }
}
