<?php

namespace Pim\Product\Model\ResourceModel\ProductBarCode;

use \Pim\Product\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{

    protected $_idFieldName = 'Id';

    public function _construct()
    {
        $this->_init(\Pim\Product\Model\ProductBarCode::class, \Pim\Product\Model\ResourceModel\ProductBarCode::class);
        $this->_map['fields']['Id'] = 'main_table.Id';
    }


    
}
