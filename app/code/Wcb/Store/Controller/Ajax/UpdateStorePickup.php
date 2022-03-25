<?php /**
 * Copyright © 2021 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wcb\Store\Controller\Ajax;

class UpdateStorePickup extends \Magento\Framework\App\Action\Action
{
    protected $resultJsonFactory;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Wcb\Store\Model\AddStoreToQuote $addStoreToQuote

    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->addStoreToQuote = $addStoreToQuote;
        return parent::__construct($context);
    }

    public function execute()
    {   
        $data = true;
		$status = "";
		$storeData = $this->getRequest()->getParams();
		if($storeData){
				
				$status = $this->addStoreToQuote->setStore($storeData);

		}
		
        
        if(empty($status)){
			$data = false;
		}
        $result = $this->resultJsonFactory->create();
        $result->setData(array('success' => $status));
        return $result;
    }

    public function getOrder($id)
    {
        return $this->orderRepository->get($id);
    }

}
