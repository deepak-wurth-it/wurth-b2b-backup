<?php

namespace Amasty\Promo\Model\ResourceModel\Rule;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\Amasty\Promo\Model\Rule::class, \Amasty\Promo\Model\ResourceModel\Rule::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
