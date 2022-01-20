<?php

namespace Amasty\Conditions\Model\ResourceModel;

use Amasty\Conditions\Api\Data\QuoteInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Quote extends AbstractDb
{
    const TABLE_NAME = 'amasty_conditions_quote';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, QuoteInterface::ITEM_ID);
    }
}
