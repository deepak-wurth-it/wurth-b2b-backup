<?php

namespace WurthNav\Sales\Model\ResourceModel\SalesShipmentLine;

use \WurthNav\Sales\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection {

    protected $_idFieldName = 'Id';

    public function _construct() {
        $this->_init(\WurthNav\Customer\Model\SalesShipmentLine::class, \WurthNav\Customer\Model\ResourceModel\SalesShipmentLine::class);
        $this->_map['fields']['Id'] = 'main_table.Id';
    }

}
