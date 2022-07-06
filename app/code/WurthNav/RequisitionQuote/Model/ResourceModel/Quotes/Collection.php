<?php

namespace WurthNav\RequisitionQuote\Model\ResourceModel\Quotes;

use WurthNav\RequisitionQuote\Model\Quotes;
use WurthNav\RequisitionQuote\Model\ResourceModel\Quotes as ResourceQuote;
use WurthNav\Sales\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{

    protected $_idFieldName = 'Id';

    public function _construct()
    {
        $this->_init(Quotes::class, ResourceQuote::class);
        $this->_map['fields']['Id'] = 'main_table.Id';
    }

}
