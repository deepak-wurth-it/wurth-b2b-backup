<?php

namespace WurthNav\Sales\Model\ResourceModel;


use WurthNav\Core\Model\ResourceModel\Core as WurthNavResourceCoreModel;

class SalesShipmentLineMiddleware extends WurthNavResourceCoreModel
{
    
    public function _construct()
    {
        $this->_init('SalesShipmentLine', 'Id');
    }
}

