<?php

namespace WurthNav\Customer\Model\ResourceModel;


use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use WurthNav\Core\Model\ResourceModel\Core as WurthNavResourceCoreModel;

class Customers extends WurthNavResourceCoreModel
{
    
    public function _construct()
    {
        $this->_init('Customers', 'Id');
    }
}

