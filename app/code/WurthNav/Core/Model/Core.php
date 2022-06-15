<?php
   
namespace WurthNav\Core\Model;

class Core extends \Magento\Framework\Model\AbstractModel{


    protected function _construct()
    {
        $this->_init(\WurthNav\Core\Model\ResourceModel\Core::class);
    }

   
}

