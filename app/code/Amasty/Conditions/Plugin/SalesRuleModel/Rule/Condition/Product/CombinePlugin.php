<?php

namespace Amasty\Conditions\Plugin\SalesRuleModel\Rule\Condition\Product;

/**
 * Add New Conditions to Product Conditions
 * @since 1.4.0
 */
class CombinePlugin
{
    /**
     * @param \Magento\SalesRule\Model\Rule\Condition\Product\Combine $subject
     * @param array $result
     *
     * @return array
     */
    public function afterGetNewChildSelectOptions(
        \Magento\SalesRule\Model\Rule\Condition\Product\Combine $subject,
        array $result
    ) {
        $groupLabel = __('Cart Item Attribute');
        $conditionAdded = false;

        foreach ($result as &$condition) {
            if (isset($condition['value'], $condition['label'])
                && is_array($condition['value'])
                && $condition['label'] === $groupLabel
            ) {
                $condition['value'][] = $this->getCustomOptionsIdOption();
                $conditionAdded = true;
                break;
            }
        }

        if (!$conditionAdded) {
            // if group of "Cart Item Attribute" not founded then add condition separately
            $result[] = $this->getCustomOptionsIdOption();
        }

        return $result;
    }

    /**
     * @return array
     */
    private function getCustomOptionsIdOption()
    {
        return [
            'value' => \Amasty\Conditions\Model\Rule\Condition\CustomOptions::class,
            'label' => __('Product Custom Options IDs')
        ];
    }
}
