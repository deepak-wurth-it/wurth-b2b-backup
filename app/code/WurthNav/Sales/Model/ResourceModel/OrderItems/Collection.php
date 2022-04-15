<?php

namespace WurthNav\Sales\Model\ResourceModel\OrderItems;

use \WurthNav\Sales\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection {

    protected $_idFieldName = 'Id';

    public function _construct() {
        $this->_init(\WurthNav\Sales\Model\OrderItems::class, \WurthNav\Sales\Model\ResourceModel\OrderItems::class);
        $this->_map['fields']['Id'] = 'main_table.Id';
    }

}
