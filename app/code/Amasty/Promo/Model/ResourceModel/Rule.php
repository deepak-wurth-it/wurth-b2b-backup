<?php
namespace Amasty\Promo\Model\ResourceModel;

use Amasty\Promo\Api\Data\GiftRuleInterface;

class Rule extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const APPLY_TO_TAX_COLUMN_NAME = 'apply_tax';
    const APPLY_TO_SHIPPING_COLUMN_NAME = 'apply_shipping';

    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amasty_ampromo_rule', GiftRuleInterface::ENTITY_ID);
    }

    /**
     * @param $ruleIds
     * @return bool
     */
    public function isApplyTax($ruleIds)
    {
        $isApplyForRules = $this->isApplicable($ruleIds, self::APPLY_TO_TAX_COLUMN_NAME);

        return count($isApplyForRules) == 1 ? (bool) $isApplyForRules[0][self::APPLY_TO_TAX_COLUMN_NAME] : false;
    }

    /**
     * @param $ruleIds
     * @return bool
     */
    public function isApplyShipping($ruleIds)
    {
        $isApplyForRules = $this->isApplicable($ruleIds, self::APPLY_TO_SHIPPING_COLUMN_NAME);

        return count($isApplyForRules) == 1 ? (bool) $isApplyForRules[0][self::APPLY_TO_SHIPPING_COLUMN_NAME] : true;
    }

    /**
     * @param $ruleIds
     * @param $column
     * @return array
     */
    public function isApplicable($ruleIds, $column)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable(), $column)
            ->where('salesrule_id in (?)', $ruleIds)
            ->group($column);

        return $connection->fetchAll($select);
    }
}
