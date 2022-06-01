<?php

namespace Wcb\Checkout\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

class ChangeTaxTotal implements ObserverInterface
{
    const XML_PATH_EMAIL_RECIPIENT = 'custom_tax_section/general/tax';
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * ChangeTaxTotal constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        $total = $observer->getData('total');
        $taxVal = $this->getTaxValue();

        if ($taxVal) {
            $vatValue = ((float)$total->getSubtotal() * $taxVal) / 100;
            $total->addTotalAmount('tax', $vatValue);
            $total->addBaseTotalAmount('tax', $vatValue);
            $total->setGrandTotal((float)$total->getGrandTotal() + $vatValue);
            $total->setBaseGrandTotal((float)$total->getBaseGrandTotal() + $vatValue);
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTaxValue()
    {
        $storeScope = ScopeInterface::SCOPE_STORE;

        return $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope);
    }
}
