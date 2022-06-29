<?php
declare(strict_types=1);

namespace Wcb\Customer\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class WurthnavEmployees extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('wurthnav_employees', 'entity_id');
    }
}
