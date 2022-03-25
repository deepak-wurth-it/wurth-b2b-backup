<?php

namespace Wcb\Store\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class SaveStoreToObserver implements ObserverInterface
{
	

    
    public function execute(EventObserver $observer)
    {
		
		$quote = $observer->getQuote();
		$order = $observer->getEvent()->getOrder();

		if ($quote) {
			$entity_id = $quote->getData('pickup_store_id');
			$name = $quote->getData("pickup_store_name");
			$contact_email = $quote->getData('pickup_store_email');
			$address = $quote->getData('pickup_store_address');
          
			if($order){
				$order->setData('pickup_store_id',$entity_id);
				$order->setData("pickup_store_name",$name);
				$order->setData('pickup_store_email',$contact_email);
				$order->setData('pickup_store_address',$address);
				$order->save();
		  }
		}
		
        return $this;
    }

}
