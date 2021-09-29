<?php

namespace Pim\Category\Model\ResourceModel;


use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Pim\Core\Model\ResourceModel\Core as PimResourceCoreModel;

class PimProductsCategories extends PimResourceCoreModel
{
    
    public function _construct()
    {
        $this->_init('productscategories', 'Id');
    }
}
