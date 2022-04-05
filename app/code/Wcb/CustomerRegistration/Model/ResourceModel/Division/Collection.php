<?php
declare(strict_types=1);

namespace Wcb\CustomerRegistration\Model\ResourceModel\Division;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Wcb\CustomerRegistration\Model\Division::class,
            \Wcb\CustomerRegistration\Model\ResourceModel\Division::class
        );
    }
}
