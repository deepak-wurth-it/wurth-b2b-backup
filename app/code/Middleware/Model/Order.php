<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Embitel\Sap\Model;

class Order extends \Magento\Framework\Model\AbstractModel
{
	
    protected $transCollectionFactory;
	public function __construct(
		\Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory $transCollectionFactory
	){
        $this->transCollectionFactory = $transCollectionFactory;
	}
	
	public function generateSapFile($invoice,$transaction = null) {
		$order = $invoice->getOrder();
		$customerAddress = $this->getCustomerAddress($invoice,$order);
		$header = $this->getHeader($order,$transaction);
		$statusContent = $this->getStatusContent($order);
		$validDateContent = $this->getValidDateContent($order);
		$paymentContent = $this->getPaymentContent($invoice,$order);
		$itemsContent = $this->getItemsContent($order);
		$vendorRevContent = $this->getVendorRevContent($order);
		$xmlhearder = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:sap-com:document:sap:rfc:functions"><soapenv:Header/><soapenv:Body><urn:ZFMFIONLSERVICE>';
		$xmlfooter = "</urn:ZFMFIONLSERVICE></soapenv:Body></soapenv:Envelope>";
		$xmlcontent = $xmlhearder. $customerAddress . $header . $statusContent. $validDateContent. $paymentContent. $itemsContent. $vendorRevContent. $xmlfooter;
		return $xmlcontent;
	}
	
	public function generateReconcelationFile($order,$transaction = null) {
		$reconcelation_data = $this->getReconcelationContent($order,$transaction);
		$xmlhearder = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:sap-com:document:sap:soap:functions:mc-style"><soapenv:Header/><soapenv:Body><urn:Zfmnetbnkdt><IFinal>';
		$xmlfooter = "</IFinal></urn:Zfmnetbnkdt></soapenv:Body></soapenv:Envelope>";
		$xmlcontent = $xmlhearder. $reconcelation_data . $xmlfooter;
		return $xmlcontent;
	}

	public function getCustomerAddress($invoice,$order) {
		 $incrementId = $order->getIncrementId();
		 $name = $order->getCustomerName();
		 $address = $order->getBillingAddress();
		 $street = $address->getStreet();
		 $region = $address->getRegion();
		 $city = $address->getCity();
		 if(!$region)$region = "Kerala";
		 $telephone = $address->getTelephone();
		 $email = $address->getEmail();
		 $postcode = $address->getPostcode();
		 $country = "India";
		 $items = $invoice->getAllItems();
		 $content = '';
         foreach($items as $item){
			$orderItem = $item->getOrderItem();
			$street0 = '';
			$street1 = '';
			if(isset($street[0]))$street0 = $street[0];
			if(isset($street[1]))$street1 = $street[1];
			$content .= "<CUSTADDR><item><ONL_DOCNO>". $incrementId ."</ONL_DOCNO><ITEM_ID>".$orderItem->getId()."</ITEM_ID><PRD_ID>".$orderItem->getProductId()."</PRD_ID><ADD_TYPE>C</ADD_TYPE><BNAME1>". $name ."</BNAME1><BADDR1>". $street0 ."</BADDR1><BADDR2>" .$street1 ."</BADDR2><BADDR3></BADDR3><BCITY>". $city ."</BCITY><BBEZEI>". $region ."</BBEZEI><BCOUNTRY>". $country ."</BCOUNTRY><BPH_MOB>". $telephone ."</BPH_MOB><BEMAIL_ID>". $email ."</BEMAIL_ID><PHONE></PHONE><PIN_CODE>". $postcode ."</PIN_CODE><B_GSTIN></B_GSTIN><B_PAN></B_PAN><B_AADHAR></B_AADHAR><B_TAX></B_TAX><IFSC_CODE></IFSC_CODE><ACTNO></ACTNO></item></CUSTADDR>";
		 }
		 return $content;
	}

	public function getHeader($order,$transaction) {
		$date = date('Y-m-d', strtotime($order->getCreatedAt()));
		$incrementId = $order->getIncrementId();
		$customerId = $order->getCustomerId();
		$tr_type = '';
		if($transaction == "refund"){
			if($order->canCreditmemo()){
				$tr_type = "J";
			}else{
				$tr_type = "R";
			}
		}else{
			$tr_type = "P";
		}
        $content = "<HEADER><item><TRTYPE>". $tr_type ."</TRTYPE><SERVICE>DEAL</SERVICE><TR_DATE>". $date ."</TR_DATE><ONL_DOCNO>". $incrementId ."</ONL_DOCNO><USERID>". $customerId ."</USERID></item></HEADER>";
		return $content;
	}

	public function getStatusContent($order) {
        $content = "<ONLORDSTATUS><item><ONL_DOCNO></ONL_DOCNO><MTTID></MTTID><ITEM_ID></ITEM_ID><WRBTR></WRBTR><USERID></USERID><STATUS></STATUS><MESSAGE></MESSAGE></item></ONLORDSTATUS>";		
		 return $content;
	}

	public function getValidDateContent($order) { 
         $content = "<ONLVALIDATESTAT><item><ONL_DOCNO></ONL_DOCNO><ITEM_ID></ITEM_ID><USERID></USERID><STATUS></STATUS><MESSAGE></MESSAGE></item></ONLVALIDATESTAT>";		
		 return $content;
	
	}

	public function getPaymentContent($invoice,$order) { 
		$incrementId = $order->getIncrementId();
		$items = $invoice->getAllItems();
		$content = '';		
		$collection = $this->transCollectionFactory->create();
		$collection->addOrderIdFilter($order->getId());
		$transactionId = '';
		if($collection->getSize() > 0){
			$transactionItem = $collection->getFirstItem();
			$transactionId = $transactionItem->getTxnId();
		}
		if($order->getPayment()->getMethodInstance()->getCode() == \Embitel\Techprocess\Model\Techprocess::PAYMENT_METHOD_TECHPROCESS_CODE){
			$pg = "TECH";
			$zwel = "N";
		}else if($order->getPayment()->getMethodInstance()->getCode() == \Razorpay\Magento\Model\PaymentMethod::METHOD_CODE){
			$pg = "RAZOR";
			$zwel = "N";
		}else if($order->getPayment()->getMethodInstance()->getCode() == \Magento\OfflinePayments\Model\Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE){
			$pg = "CASH";
			$zwel = "S";
		}else if($order->getPayment()->getMethodInstance()->getCode() == \Embitel\OfflinePayments\Model\SwipemachinePos::PAYMENT_METHOD_SWIPEMACHINE_CODE){
			$pg = "SWIPE";
			$zwel = "W";
		}else{
			$pg = "CASH";
			$zwel = "S";
		}
        foreach($items as $item){			
			$orderItem = $item->getOrderItem();
			$total = $orderItem->getRowTotal() - $orderItem->getDiscountAmount();
			$productName = mb_substr($orderItem->getName(), 0, 48);
			$content .= "<PAYMENT><item><ONL_DOCNO>". $incrementId ."</ONL_DOCNO><ITEM_ID>".$orderItem->getId()."</ITEM_ID><PRD_ID>".$orderItem->getProductId()."</PRD_ID><PRD_DESP>".$productName."</PRD_DESP><ZWELS>".$zwel."</ZWELS><PG>".$pg."</PG><MID></MID><MTTID>".$transactionId."</MTTID><PGTID></PGTID><AUTID></AUTID><BANK_NAME></BANK_NAME><ZRRN></ZRRN><WRBTR>".$total."</WRBTR><WAERS>INR</WAERS><ETMENGE></ETMENGE><VKBUR></VKBUR><GSBER>KTM</GSBER><UNAME></UNAME><TRANS_ID></TRANS_ID></item></PAYMENT>";	
		}
		return $content;
	}

	public function getItemsContent($order) { 
		$incrementId = $order->getIncrementId();
		$content = "<VENDORPAY><item><PRD_ID></PRD_ID><ITEM_ID></ITEM_ID><SR_NO></SR_NO><PAY_TYPE></PAY_TYPE><LIFNR></LIFNR><KBETR_P></KBETR_P><KBETR_A></KBETR_A><PERNR></PERNR><WRBTR></WRBTR><FROMDATE></FROMDATE><TODATE></TODATE></item></VENDORPAY>";	
		return $content;
	} 

	public function getVendorRevContent($order) {  
		$incrementId = $order->getIncrementId();
		$content = "<VENDORREV><item><ONL_DOCNO></ONL_DOCNO><ITEM_ID></ITEM_ID><SR_NO></SR_NO><ELIFNR></ELIFNR><WRBTR></WRBTR><STATUS></STATUS> <USERID></USERID><PRD_ID></PRD_ID></item></VENDORREV>";	
		return $content;		
	}

	public function getReconcelationContent($order,$transaction) { 
		$incrementId = $order->getIncrementId();
		$date = date('Y-m-d', strtotime($order->getCreatedAt()));
		$customerId = $order->getCustomerId();
		
		$tstat = '';
		if($transaction == "refund"){
			$tstat = "M";
			$message = "Transaction Refunded";
		}else{			
			if($order->getStatus() == \Magento\Sales\Model\Order::STATE_CANCELED){
				$tstat = "F";
				$message = "Transaction Canceled";
			}else{
				$tstat = "S";
				$message = "Transaction Successful";
			}
		}
		$collection = $this->transCollectionFactory->create();
		$collection->addOrderIdFilter($order->getId());
		$transactionId = '';
		if($collection->getSize() > 0){
			$transactionItem = $collection->getFirstItem();
			$transactionId = $transactionItem->getTxnId();
		}
		if($order->getPayment()->getMethodInstance()->getCode() == \Embitel\Techprocess\Model\Techprocess::PAYMENT_METHOD_TECHPROCESS_CODE){
			$pg = "TECH";
			$zwel = "N";
		}else if($order->getPayment()->getMethodInstance()->getCode() == \Razorpay\Magento\Model\PaymentMethod::METHOD_CODE){
			$pg = "RAZOR";
			$zwel = "N";
		}else if($order->getPayment()->getMethodInstance()->getCode() == \Magento\OfflinePayments\Model\Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE){
			$pg = "CASH";
			$zwel = "S";
		}else if($order->getPayment()->getMethodInstance()->getCode() == \Embitel\OfflinePayments\Model\SwipemachinePos::PAYMENT_METHOD_SWIPEMACHINE_CODE){
			$pg = "SWIPE";
			$zwel = "W";
		}else{
			$pg = "CASH";
			$zwel = "S";
		}
		$address = $order->getBillingAddress();
		$email = $address->getEmail();		
		$name = $order->getCustomerName();
		$street = $address->getStreet();
		$street0 = '';
		$street1 = '';
		if(isset($street[0]))$street0 = $street[0];
		if(isset($street[1]))$street1 = $street[1];
		$region = $address->getRegion();
		$city = $address->getCity();
		if(!$region)$region = "Kerala";
		$telephone = $address->getTelephone();
		$postcode = $address->getPostcode();
		$country = "India";

		$total = round($order->getGrandTotal(),2);
		$content = "<item><Stat></Stat><Tstat>". $tstat ."</Tstat><Rstat></Rstat><Pstat></Pstat><Mid></Mid><Crdt>". $date ."</Crdt><Mttid>". $transactionId ."</Mttid><Hbkid></Hbkid><Bukrs></Bukrs><Vkbur></Vkbur><Pg>". $pg ."</Pg><Rrnid></Rrnid><Autid></Autid><Pgtid></Pgtid><Ordid>". $incrementId ."</Ordid><Wrbtr>".$total."</Wrbtr><Waers>INR</Waers><Cusid>". $customerId ."</Cusid><Cusn>". $email ."</Cusn><Custi></Custi><Name>". $name."</Name><Address1>". $street0 ."</Address1><Address2>". $street1 ."</Address2><City>". $city ."</City><State>". $region ."</State><Country>". $country ."</Country><Zip>". $postcode ."</Zip><TelNum>". $telephone ."</TelNum><Email>". $email ."</Email><Rdoc></Rdoc><Fidoc></Fidoc><Pdate></Pdate><ReconUser></ReconUser><RefundUser></RefundUser><RefundIm></RefundIm><RefundEx></RefundEx><RejectRs></RejectRs><Updusr></Updusr><Upddt></Upddt><Updti></Updti><FilNam></FilNam><Type></Type><Message>". $message ."</Message></item>";
		return $content;
	}
}
