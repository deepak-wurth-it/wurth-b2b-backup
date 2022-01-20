<?php

namespace WurthNav\Sales\Model\ResourceModel\SalesShipmentLineMiddleware;

use \WurthNav\Sales\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection {

    protected $_idFieldName = 'Id';

    public function _construct() {
        $this->_init(\WurthNav\Sales\Model\SalesShipmentLineMiddleware::class, \WurthNav\Sales\Model\ResourceModel\SalesShipmentLineMiddleware::class);
        $this->_map['fields']['Id'] = 'main_table.Id';
    }

}
