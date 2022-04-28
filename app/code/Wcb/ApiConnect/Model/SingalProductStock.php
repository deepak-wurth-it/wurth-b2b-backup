<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wcb\ApiConnect\Model;

class SingalProductStock implements \Wcb\ApiConnect\Api\SingalProductStockInterface
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
		//return parent::__construct($context);
	}

	public function callSingalProductStock($sku)
	{


		$xmlData = $this->getSingleStock($sku);
		if($xmlData){
			$xmlData = $xmlData->SoapBody->GetItemAvailabilityOnLocationEShop_Result;
			$data = $xmlData;;
			$data = (array) $data;
			$data = json_encode($data);

		}

     return $data;

	}

	public function getSingleStock($sku=null){

		return $this->_soapApiClient->GetItemAvailabilityOnLocation($sku);

	}

}
