<?php

namespace Amasty\Promo\Plugin;

use Magento\SalesRule\Model\Rule\Metadata\ValueProvider as SalesRuleValueProvider;

class ValueProvider
{
    /**
     * @param SalesRuleValueProvider $subject
     * @param array $result
     *
     * @return array
     */
    public function afterGetMetadataValues(
        SalesRuleValueProvider $subject,
        array $result
    ) {
        $actions = &$result['actions']['children']['simple_action']['arguments']['data']['config']['options'];
        $autoAddActions = [
            [
                'label' => __('Auto add promo items with products'),
                'value' => \Amasty\Promo\Api\Data\GiftRuleInterface::PER_PRODUCT
            ],
            [
                'label' => __('Auto add promo items for the whole cart'),
                'value' => \Amasty\Promo\Api\Data\GiftRuleInterface::WHOLE_CART
            ],
            [
                'label' => __('Auto add the same product'),
                'value' => \Amasty\Promo\Api\Data\GiftRuleInterface::SAME_PRODUCT
            ],
            [
                'label' => __('Auto add promo items for every $X spent'),
                'value' => \Amasty\Promo\Api\Data\GiftRuleInterface::SPENT
            ],
            [
                'label' => __('Add gift with each N-th product in the cart'),
                'value' => \Amasty\Promo\Api\Data\GiftRuleInterface::EACHN
            ]
        ];

        $actions[] = [
            'label' => __('Automatically add products to cart'),
            'value' => $autoAddActions
        ];

        return $result;
    }
}
