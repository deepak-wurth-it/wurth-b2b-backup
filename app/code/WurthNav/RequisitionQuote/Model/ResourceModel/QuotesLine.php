<?php

namespace WurthNav\RequisitionQuote\Model\ResourceModel;

use WurthNav\Core\Model\ResourceModel\Core as WurthNavResourceCoreModel;

class QuotesLine extends WurthNavResourceCoreModel
{
    public function _construct()
    {
        $this->_init('QuotesLine', 'Id');
    }
}

