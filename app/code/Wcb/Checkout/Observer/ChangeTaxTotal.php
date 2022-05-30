<?php

namespace Wcb\Checkout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ChangeTaxTotal implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $total = $observer->getData('total');
        $vatValue = ((float)$total->getSubtotal() * 25) / 100;
        $total->addTotalAmount('tax', $vatValue);
        $total->addBaseTotalAmount('tax', $vatValue);
        $total->setGrandTotal((float)$total->getGrandTotal() + $vatValue);
        $total->setBaseGrandTotal((float)$total->getBaseGrandTotal() + $vatValue);

        return $this;
    }
}
