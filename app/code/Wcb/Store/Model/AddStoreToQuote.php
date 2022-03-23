<?php

namespace Wcb\Store\Model;

use Magento\Framework\Model\AbstractModel;

class AddStoreToQuote extends AbstractModel
{
   
    public function __construct(
        \Magento\Quote\Api\Data\CartInterface $quote,
        \Magento\Checkout\Model\Session $checkoutSession  
    ) {
       
        $this->quote = $quote;
        $this->checkoutSession = $checkoutSession;
    }



    public function setStore($store) {
        $cartExtension = $this->getQuotes()->getExtensionAttributes();
        if ($cartExtension) {
            $cartExtension->setPickupStoreId($store['entity_id']);
			$cartExtension->setPickupStoreName($store['name']);
			$cartExtension->setPickupStoreEmail($store['contact_email']);
			$cartExtension->setPickupStoreAddress($store['address']);
			$this->quote->setExtensionAttributes($cartExtension);
			return true;
        }
        return false;
       
    }
    
    public function getQuotes()
    {
            
            return $this->checkoutSession->getQuote();

       
    }
}
