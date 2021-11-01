<?php

namespace Wcb\PromotionBanner\Model\ResourceModel\PromotionBanner;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';
    /**
     * Define resource model.
     */
    protected function _construct()
    {
        $this->_init(
            'Wcb\PromotionBanner\Model\PromotionBanner',
            'Wcb\PromotionBanner\Model\ResourceModel\PromotionBanner'
        );
    }
}
