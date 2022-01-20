<?php

namespace Amasty\Promo\Model\Config\Source\Selection;

/**
 * Popup behavior mode
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
            \Amasty\Promo\Block\Popup::POPUP_ONE_BY_ONE => __('One By One'),
            \Amasty\Promo\Block\Popup::POPUP_MULTIPLE => __('Multiple')
        ];
    }
}
