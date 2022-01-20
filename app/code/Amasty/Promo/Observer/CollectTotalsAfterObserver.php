<?php

namespace Amasty\Promo\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * @since 2.5.4 replaced with plugin
 * @see \Amasty\Promo\Plugin\Quote\Model\Quote\TotalsCollectorPlugin
 * TODO delete this file after 12 November 2019
 */
class CollectTotalsAfterObserver implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(Observer $observer)
    {
        throw new \LogicException('Trying to execute old code. Please, clear the cache.');
    }
}
