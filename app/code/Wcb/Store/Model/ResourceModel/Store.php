<?php

namespace Wcb\Store\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;


class Store extends AbstractDb
{

    public function _construct()
    {
        $this->_init('wcb_store_pickup', 'entity_id');
    }
}
