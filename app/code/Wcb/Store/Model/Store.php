<?php

namespace Wcb\Store\Model;

use Magento\Framework\Model\AbstractModel;

class Store extends AbstractModel
{
    protected $_dateTime;

   
    protected function _construct()
    {
        $this->_init(\Wcb\Store\Model\ResourceModel\Store::class);
    }
    
}