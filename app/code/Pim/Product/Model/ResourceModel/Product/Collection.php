<?php

namespace Pim\Product\Model\ResourceModel\Product;

use \Pim\Product\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection {

    protected $_idFieldName = 'Id';

    public function _construct() {
        $this->_init(\Pim\Product\Model\Product::class, \Pim\Product\Model\ResourceModel\Product::class);
        $this->_map['fields']['Id'] = 'main_table.Id';
    }

}
