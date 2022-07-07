<?php

namespace WurthNav\RequisitionQuote\Model\ResourceModel\QuotesLine;

use WurthNav\RequisitionQuote\Model\QuotesLine;
use WurthNav\RequisitionQuote\Model\ResourceModel\QuotesLine as ResourceQuoteLine;
use WurthNav\Sales\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{

    protected $_idFieldName = 'Id';

    public function _construct()
    {
        $this->_init(QuotesLine::class, ResourceQuoteLine::class);
        $this->_map['fields']['Id'] = 'main_table.Id';
    }

}
