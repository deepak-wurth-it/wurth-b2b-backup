<?php

namespace Pim\Core\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;


class Core extends AbstractDb
{
    public function _construct()
    {
        $this->_init('vendors', 'Id');
    }
     
}
