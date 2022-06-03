<?php

namespace Pim\Product\Model\ResourceModel;


use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Pim\Core\Model\ResourceModel\Core as PimResourceCoreModel;

class ProductPdf extends PimResourceCoreModel
{

    public function _construct()
    {
        $this->_init('productspdfs', 'Id');
    }
}
