<?php

namespace Amasty\BannersLite\Model\ResourceModel\Banner;

use \Amasty\BannersLite\Api\Data\BannerInterface;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\Amasty\BannersLite\Model\Banner::class, \Amasty\BannersLite\Model\ResourceModel\Banner::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * @param array $ruleIds
     *
     * @return array
     */
    public function getBySalesruleIds($ruleIds)
    {
        $this->addFieldToFilter(BannerInterface::SALESRULE_ID, ['in' => $ruleIds]);

        return $this->_fetchAll($this->getSelect());
    }
}
