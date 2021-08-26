<?php

namespace Pim\Category\Model\ResourceModel\Category;

use \Pim\Category\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection {

    protected $_idFieldName = 'Id';

    public function _construct() {
        $this->_init(\Pim\Category\Model\Category::class, \Pim\Category\Model\ResourceModel\Category::class);
        $this->_map['fields']['Id'] = 'main_table.Id';
    }

}
