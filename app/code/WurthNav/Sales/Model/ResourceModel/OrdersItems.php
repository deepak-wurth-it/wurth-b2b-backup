<?php

namespace WurthNav\Sales\Model\ResourceModel;


use WurthNav\Core\Model\ResourceModel\Core as WurthNavResourceCoreModel;

class OrdersItems extends WurthNavResourceCoreModel
{
    
    public function _construct()
    {
        $this->_init('OrderItems', 'Id');
    }
}

