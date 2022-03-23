<?php

namespace Wcb\Store\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class SaveDataToOrderObserver implements ObserverInterface
{
	
	
	public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
		\Magento\Framework\Event\Manager $eventManager
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->eventManager = $eventManager;
    }
    
    public function execute(EventObserver $observer)
    {
		$cartExtension = $this->getQuotes()->getExtensionAttributes();
		$order = $observer->getOrder();
		if ($cartExtension) {
			$entity_id = $cartExtension->setPickupStoreId($store);
			$name = $cartExtension->setPickupStoreName();
			$contact_email = $cartExtension->setPickupStoreEmail();
			$address = $cartExtension->setPickupStoreAddress();
			
			$orderExtension = $order->getExtensionAttributes();
			
			if($orderExtension){
				
				$orderExtension->setPickupStoreId($entity_id);
				$orderExtension->setPickupStoreName($name);
				$orderExtension->setPickupStoreEmail($contact_email);
				$orderExtension->setPickupStoreAddress($address);
			    $order->setExtensionAttributes($orderExtension);

		  }
		}
		
        return $this;
    }
}
