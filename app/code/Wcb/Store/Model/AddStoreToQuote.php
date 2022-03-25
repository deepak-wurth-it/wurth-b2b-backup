<?php

namespace Wcb\Store\Model;

use Magento\Framework\Model\AbstractModel;

class AddStoreToQuote extends AbstractModel
{
   
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\QuoteRepository $quoteRepository

  
    ) {
       $this->checkoutSession = $checkoutSession;
       $this->quoteRepository = $quoteRepository;

    }



    public function setStore($store) {
        $quote = $this->getQuotes();
        $action =  $store['action'];
        $quoteId = $quote->getId();
        $quote = $this->quoteRepository->get($quoteId);
        if ($quote && $action == 1 ) {
            
                $quote->setData('pickup_store_id',$store['entity_id']);
				$quote->setData('pickup_store_name',$store['name']);
				$quote->setData('pickup_store_email',$store['contact_email']);
				$quote->setData('pickup_store_address',$store['address']);
                $this->quoteRepository->save($quote);
                return json_encode($store['address']);
			return '1';
        }else{
			
			    $quote->setData('pickup_store_id',"");
				$quote->setData('pickup_store_name',"");
				$quote->setData('pickup_store_email',"");
				$quote->setData('pickup_store_address',"");
                $this->quoteRepository->save($quote);
                return '2';
			
		}
        return false;
       
    }
    
    public function getQuotes()
    {
            
            return $this->checkoutSession->getQuote();

       
    }
}
