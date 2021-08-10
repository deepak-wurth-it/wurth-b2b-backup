<?php

namespace Pim\Category\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;


class Category extends AbstractDb
{
    
    public function _construct()
    {
        $this->_init('categories', 'Id');
    }
}
