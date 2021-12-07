<?php /**
 * Copyright © 2021 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wcb\Catalog\Controller\Ajax;
class SingalProductPrice extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	protected $resultJsonFactory;
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Wcb\ApiConnect\Model\Soap\ApiClient $soapApiClient,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
		)
	{
		$this->_pageFactory = $pageFactory;
		$this->_soapApiClient = $soapApiClient;
		return parent::__construct($context);
	}

	public function execute()
	{
		 /** @var \Magento\Framework\Controller\Result\Json $result */
		 //$result = $this->resultJsonFactory->create();
		 //$xmlPrice = $this->getSinglePrice();
		 //$data = simplexml_load_string($xmlPrice, "SimpleXMLElement", LIBXML_NOCDATA);
		 //$result->setData($data);
         //return $result;
	}

	public function getSinglePrice($sku='001 512'){
		
		$this->_soapApiClient->GetItemAvailabilityOnLocation($sku);

	}
}