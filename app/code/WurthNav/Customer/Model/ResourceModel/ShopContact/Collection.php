<?php

namespace WurthNav\Customer\Model\ResourceModel\ShopContact;

use \WurthNav\Customer\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection {

    protected $_idFieldName = 'Id';

    public function _construct() {
        $this->_init(\WurthNav\Customer\Model\ShopContact::class, \WurthNav\Customer\Model\ResourceModel\ShopContact::class);
        $this->_map['fields']['Id'] = 'main_table.Id';
    }

}
