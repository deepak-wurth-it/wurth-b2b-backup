<?php

namespace Amasty\Promo\Test\Unit\Traits;

/**
 * Create Object Manager instance for test purposes.
 *
 * phpcs:ignoreFile
 */
trait ObjectManagerTrait
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @return \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private function getObjectManager()
    {
        if (!$this->objectManager) {
            $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        }

        return $this->objectManager;
    }
}
