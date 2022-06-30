<?php

namespace Wcb\Customer\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class WurthnavEmployees extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'wurth_nav_employees';
    /**
     * @var string
     */
    protected $_cacheTag = 'wurth_nav_employees';
    /**
     * @var string
     */
    protected $_eventPrefix = 'wurth_nav_employees';

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('Wcb\Customer\Model\ResourceModel\WurthnavEmployees');
    }
}
