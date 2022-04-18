<?php

namespace Pim\Product\Model\ResourceModel;


use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Pim\Core\Model\ResourceModel\Core as PimResourceCoreModel;

class ProductImages extends PimResourceCoreModel
{
    const PIC_TABLE = 'productspictures';
    const UPDATE_FIELD = 'UpdateRequired';
    const UPDATE_WHERE = 'Id';

    public function _construct()
    {
        $this->_init('productspictures', 'Id');
    }

   

    public function updateByQuery($id){
      $this->getConnection()->query("update productspictures set UpdateRequired=0 where Id=$id");
    }
}
