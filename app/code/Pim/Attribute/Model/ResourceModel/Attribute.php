<?php

namespace Pim\Attribute\Model\ResourceModel;


use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Pim\Core\Model\ResourceModel\Core as PimResourceCoreModel;

class Attribute extends PimResourceCoreModel
{
    
    public function _construct()
    {
        $this->_init('attributes', 'Id');
    }
}
