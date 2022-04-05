<?php
declare(strict_types=1);

namespace Wcb\CustomerRegistration\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Division extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('division', 'id');
    }
}
