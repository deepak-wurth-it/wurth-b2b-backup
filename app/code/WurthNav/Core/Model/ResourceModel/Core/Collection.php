<?php

namespace WurthNav\Core\Model\ResourceModel\Core;

use \WurthNav\Core\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection {

    //protected $_idFieldName = 'Id';

    public function _construct() {
        $this->_init(\WurthNav\Core\Model\Core::class, \WurthNav\Core\Model\ResourceModel\Core::class);
        //$this->_map['fields']['Id'] = 'main_table.Id';
    }

}
