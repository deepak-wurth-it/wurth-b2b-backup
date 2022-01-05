<?php

namespace WurthNav\Customer\Model\ResourceModel;


use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use WurthNav\Core\Model\ResourceModel\Core as WurthNavResourceCoreModel;

class ShopContact extends WurthNavResourceCoreModel
{
    
    public function _construct()
    {
        $this->_init('Shop_Contact', 'Id');
    }
}

