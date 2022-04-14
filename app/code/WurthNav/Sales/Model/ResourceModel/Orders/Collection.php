<?php

namespace WurthNav\Sales\Model\ResourceModel\Orders;

use \WurthNav\Sales\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection {

    protected $_idFieldName = 'Id';

    public function _construct() {
        $this->_init(\WurthNav\Sales\Model\Orders::class, \WurthNav\Sales\Model\ResourceModel\Orders::class);
        $this->_map['fields']['Id'] = 'main_table.Id';
    }

}
