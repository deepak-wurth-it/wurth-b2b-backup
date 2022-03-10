<?php

namespace Wcb\Store\Model\ResourceModel\Store;

use \Wcb\Store\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection {

    protected $_idFieldName = 'entity_id';

    public function _construct() {
        $this->_init(\Wcb\Store\Model\Store::class, \Wcb\Store\Model\ResourceModel\Store::class);
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }

}
