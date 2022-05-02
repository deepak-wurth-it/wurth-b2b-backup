<?php /**
 * Copyright © 2021 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wcb\Catalog\Controller\Ajax;
class GetItemAvailabilityOnLocation extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	protected $resultJsonFactory;
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Wcb\ApiConnect\Model\SoapClient $soapApiClient,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $dateInt
		)
	{
		$this->_pageFactory = $pageFactory;
		$this->_soapApiClient = $soapApiClient;
		$this->resultJsonFactory = $resultJsonFactory;
		$this->date = $date;
		$this->dateInt = $dateInt;

		return parent::__construct($context);
	}

	public function execute()
	{

		$sku = $this->getRequest()->getParam('sku');
		/** @var \Magento\Framework\Controller\Result\Json $result */
		$result = $this->resultJsonFactory->create();
		$xmlData = $this->getSingleStock($sku);
		if($xmlData){
			$xmlData = $xmlData->SoapBody->GetItemAvailabilityOnLocationEShop_Result;
			$data = $xmlData;;
			$data = (array) $data;
		}

		if($data['availabilityOnDateP']){
		 $stockDate = $data['availabilityOnDateP'];
		 $diff = $this->getDifference($stockDate);
		 
		 if($diff > 0 ){
			$data['remain_days'] = $diff;
		 }
		 if($diff < 0 ){
			$data['remain_days'] = 0;
		 }
		  
		}
		
		$result->setData(array('success'=>$data));
		return $result;
    }

	public function getSingleStock($sku){
		
		return $this->_soapApiClient->GetItemAvailabilityOnLocation($sku);

	}

	public function getDifference($stockDate)
{
	$stockDate = date("Y-m-d", strtotime($stockDate));
    $toDate = strtotime($stockDate);
	$current = date("Y-m-d", strtotime("now"));
	$fromDate = strtotime($current);
	$secs =  $toDate-$fromDate;// == <seconds between the two times>
	$days = $secs / 86400;
    return $days;
}
}