<?php /**
 * Copyright © 2021 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wcb\Store\Controller\Ajax;
use Wurth\Shippingproduct\Helper\AddRemoveShippingProduct as ShippingproductHelper;

class UpdateStorePickup extends \Magento\Framework\App\Action\Action
{
    protected $resultJsonFactory;

    protected $shippingproductHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Wcb\Store\Model\AddStoreToQuote $addStoreToQuote,
        ShippingproductHelper $shippingproductHelper

    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->addStoreToQuote = $addStoreToQuote;
        $this->shippingproductHelper = $shippingproductHelper;
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
        
       // $this->shippingproductHelper->updateShippingProduct();

        $result = $this->resultJsonFactory->create();
        $result->setData(array('success' => $status));
        return $result;
    }

    public function getOrder($id)
    {
        return $this->orderRepository->get($id);
    }

}
