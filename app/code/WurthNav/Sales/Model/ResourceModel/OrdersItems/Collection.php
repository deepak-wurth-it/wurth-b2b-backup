<?php

namespace WurthNav\Sales\Model\ResourceModel\OrdersItems;

use \WurthNav\Sales\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection {

    protected $_idFieldName = 'Id';

    public function _construct() {
        $this->_init(\WurthNav\Sales\Model\OrdersItems::class, \WurthNav\Sales\Model\ResourceModel\OrdersItems::class);
        $this->_map['fields']['Id'] = 'main_table.Id';
    }

}
