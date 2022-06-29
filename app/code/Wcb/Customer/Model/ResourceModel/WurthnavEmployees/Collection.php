<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wcb\Customer\Model\ResourceModel\WurthnavEmployees;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Wcb\Customer\Model\ResourceModel\WurthnavEmployees;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init(
            \Wcb\Customer\Model\WurthnavEmployees::class,
            WurthnavEmployees::class
        );
    }
}
