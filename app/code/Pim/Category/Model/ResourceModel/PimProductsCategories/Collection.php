<?php

namespace Pim\Category\Model\ResourceModel\PimProductsCategories;

use \Pim\Category\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection {

    protected $_idFieldName = 'Id';

    public function _construct() {
        $this->_init(\Pim\Category\Model\PimProductsCategories::class, \Pim\Category\Model\ResourceModel\PimProductsCategories::class);
        $this->_map['fields']['Id'] = 'main_table.Id';
    }

}
