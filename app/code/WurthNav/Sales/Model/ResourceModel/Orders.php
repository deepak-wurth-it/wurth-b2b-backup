<?php

namespace WurthNav\Sales\Model\ResourceModel;


use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use WurthNav\Core\Model\ResourceModel\Core as WurthNavResourceCoreModel;


class Orders extends WurthNavResourceCoreModel
{
    
    public function _construct()
    {
        $this->_init('Orders', 'Id');
    }
}

