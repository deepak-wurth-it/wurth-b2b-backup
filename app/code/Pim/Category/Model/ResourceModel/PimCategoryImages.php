<?php

namespace Pim\Category\Model\ResourceModel;


use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Pim\Core\Model\ResourceModel\Core as PimResourceCoreModel;

class PimCategoryImages extends PimResourceCoreModel
{
    
    public function _construct()
    {
        $this->_init('categoriespictures', 'Id');
    }
    
     public function updateByQuery($id){
      $this->getConnection()->query("update categoriespictures set UpdateRequired=0 where Id=$id");
    }
}
