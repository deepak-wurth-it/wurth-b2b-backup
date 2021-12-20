<?php /**
 * Copyright © 2021 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wcb\Catalog\Controller\Ajax;
class SingalProductStock extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	protected $resultJsonFactory;
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Wcb\ApiConnect\Model\SoapClient $soapApiClient,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
		)
	{
		$this->_pageFactory = $pageFactory;
		$this->_soapApiClient = $soapApiClient;
		$this->resultJsonFactory = $resultJsonFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
		 /** @var \Magento\Framework\Controller\Result\Json $result */
		 $result = $this->resultJsonFactory->create();
		
		 //$sku = $this->getRequest()->getPost('sku');
		 $xmlStock = $this->getSingleStock();
		 
		 $xmlStock = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $xmlStock);
         $data = simplexml_load_string($xmlStock);
		 //echo $data;
		 $result->setData(array('success'=>$data));
         return $result;



	}

	public function getSingleStock($sku='899 102310'){
		
		return $this->_soapApiClient->GetItemAvailabilityOnLocation($sku);

	}
}