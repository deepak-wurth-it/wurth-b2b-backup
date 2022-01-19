<?php

namespace Amasty\Conditions\Model\ResourceModel\Quote;

use Amasty\Conditions\Model\Quote;
use Amasty\Conditions\Model\ResourceModel\Quote as QuoteResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(Quote::class, QuoteResourceModel::class);
    }
}
