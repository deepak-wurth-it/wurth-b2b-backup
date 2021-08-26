<?php

namespace Pim\Attribute\Model\ResourceModel\AttributeValues;

use \Pim\Attribute\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection {

    protected $_idFieldName = 'Id';

    public function _construct() {
        $this->_init(\Pim\Attribute\Model\AttributeValues::class, \Pim\Attribute\Model\ResourceModel\AttributeValues::class);
        $this->_map['fields']['Id'] = 'main_table.Id';
    }

}
