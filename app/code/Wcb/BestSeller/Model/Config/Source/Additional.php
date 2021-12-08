<?php

namespace Wcb\BestSeller\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Additional
 * @package Mageplaza\AutoRelated\Model\Config\Source
 */
class Additional implements ArrayInterface
{
    const SHOW_PRICE = '1';
    const SHOW_CART = '2';
    const SHOW_REVIEW = '3';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        foreach ($this->toArray() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $options;
    }

    /**
     * @return array
     */
    protected function toArray()
    {
        return [
            self::SHOW_PRICE => __('Price'),
            self::SHOW_CART => __('Add to cart button'),
            self::SHOW_REVIEW => __('Review information')
        ];
    }
}
