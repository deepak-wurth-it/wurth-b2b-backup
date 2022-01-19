<?php

namespace Amasty\BannersLite\Model\ResourceModel\Rule;

class Collection extends \Magento\SalesRule\Model\ResourceModel\Rule\Collection
{

    /**
     * @param string $linkField
     * @param array $ruleIds
     *
     * @return array
     */
    public function getActiveRuleIds($linkField, $ruleIds)
    {
        $this->prepareSqlConditions($linkField, $ruleIds);

        return $this->getRuleIds($linkField);
    }

    /**
     * @param string $linkField
     * @param array $ruleIds
     */
    private function prepareSqlConditions($linkField, $ruleIds)
    {
        $allowedActions = [
            //SP Rules
            'thecheapest',
            'themostexpencive',
            'moneyamount',
            'eachn_perc',
            'eachn_fixdisc',
            'eachn_fixprice',
            'eachmaftn_perc',
            'eachmaftn_fixdisc',
            'eachmaftn_fixprice',
            'groupn',
            'groupn_disc',
            'buyxgety_perc',
            'buyxgety_fixprice',
            'buyxgety_fixdisc',
            'buyxgetn_perc',
            'buyxgetn_fixprice',
            'buyxgetn_fixdisc',
            'aftern_fixprice',
            'aftern_disc',
            'aftern_fixdisc',
            'setof_percent',
            'setof_fixed',
            'tiered_wholecheaper',
            'tiered_buyxgetcheapern',
            //Free Gift Rules
            'ampromo_product',
            'ampromo_items',
            'ampromo_cart',
            'ampromo_spent',
            'ampromo_eachn'
        ];

        $this->addFieldToFilter('main_table.' . $linkField, ['in' => $ruleIds]);
        $this->addFieldToFilter(\Magento\SalesRule\Model\Data\Rule::KEY_SIMPLE_ACTION, ['in' => $allowedActions]);
        $this->addIsActiveFilter();
    }

    /**
     * @param string $linkField
     *
     * @return array
     */
    private function getRuleIds($linkField)
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $idsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);

        $idsSelect->columns($linkField, 'main_table');

        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }
}
