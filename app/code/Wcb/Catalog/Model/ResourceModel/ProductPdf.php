<?php

namespace Wcb\Catalog\Model\ResourceModel;


use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ProductPdf extends AbstractDb
{
    
    public function _construct()
    {
        $this->_init('product_pdf', 'entity_id');
    }
}
