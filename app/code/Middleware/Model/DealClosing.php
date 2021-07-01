<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Embitel\Sap\Model;

class DealClosing extends \Magento\Framework\Model\AbstractModel
{
	
    protected $merchantModel;
	public function __construct(
		\Embitel\Marketplace\Model\Vendor $merchantModel,
		\Embitel\DealClosing\Model\Close $dealClose,
		\Embitel\DealClosing\Model\Executives $executives,
		\Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $itemCollectionFactory
	){
        $this->merchantModel = $merchantModel;
		$this->dealClose = $dealClose;
		$this->executives = $executives;
		$this->itemCollectionFactory = $itemCollectionFactory;
	}
	
	public function generateDealClosingFile($receipt) {
		$customerAddress = $this->getVendorAddress($receipt);
		$header = $this->getDealClosingHeader($receipt);
		$itemsContent = $this->getDealClosingItemsContent($receipt);
		$statusContent = $this->getStatusContent($receipt);
		$validDateContent = $this->getValidDateContent($receipt);
		$xmlhearder = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:sap-com:document:sap:rfc:functions"><soapenv:Header/><soapenv:Body><urn:ZFMFIONLSERVICE>';
		$xmlfooter = "</urn:ZFMFIONLSERVICE></soapenv:Body></soapenv:Envelope>";
		$xmlcontent = $xmlhearder. $customerAddress . $header . $statusContent. $validDateContent. $itemsContent. $xmlfooter;
		return $xmlcontent;
	}

	public function getVendorAddress($receipt) {
		$vendorId = $receipt->getVendorId();
		if(!$vendorId)return;
		$itemId = $receipt->getProductId();
		$incrementId = $receipt->getId();
		$merchant = $this->merchantModel->getMerchant($vendorId);
		$name = $merchant->getContactPerson();
		$street0 = $merchant->getAddress();
		$street1 = $merchant->getLocation();
		$regionId = $merchant->getRegionId();
		if($regionId){
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$regionObj = $objectManager->create('\Magento\Directory\Model\Region')
					->load($regionId);
			$region = $regionObj->getName();
		}else{
			$region = "Kerala";
		}
		$city = $merchant->getCity();
		if(!$region)$region = "Kerala";
		$telephone = $merchant->getContactNumber();
		$email = $merchant->getEmail();
		$postcode = $merchant->getZipCode();
		$gstin = $merchant->getVendorGstin();
		$pancard = $merchant->getPancard();
		$ifsccode = $merchant->getIfsc();
		$acoount_no = $merchant->getAccountNo();
		$country = "India";
		$content = '';
		$content .= "<CUSTADDR><item><ONL_DOCNO>". $incrementId ."</ONL_DOCNO><ITEM_ID>".$itemId."</ITEM_ID><PRD_ID>".$itemId."</PRD_ID><ADD_TYPE>V</ADD_TYPE><BNAME1>". $name ."</BNAME1><BADDR1>". $street0 ."</BADDR1><BADDR2>" .$street1 ."</BADDR2><BADDR3></BADDR3><BCITY>". $city ."</BCITY><BBEZEI>". $region ."</BBEZEI><BCOUNTRY>". $country ."</BCOUNTRY><BPH_MOB>". $telephone ."</BPH_MOB><BEMAIL_ID>". $email ."</BEMAIL_ID><PHONE></PHONE><PIN_CODE>". $postcode ."</PIN_CODE><B_GSTIN>". $gstin ."</B_GSTIN><B_PAN>". $pancard ."</B_PAN><B_AADHAR></B_AADHAR><B_TAX></B_TAX><IFSC_CODE>". $ifsccode ."</IFSC_CODE><ACTNO". $acoount_no ."></ACTNO></item></CUSTADDR>";
		return $content;
	}

	public function getDealClosingHeader($receipt) {
		$date = date('Y-m-d', strtotime($receipt->getClosedDate()));
		$incrementId = $receipt->getId();
		$vendorId = $receipt->getVendorId();
		$tr_type = 'D';
        $content = "<HEADER><item><TRTYPE>". $tr_type ."</TRTYPE><SERVICE>DEAL</SERVICE><TR_DATE>". $date ."</TR_DATE><ONL_DOCNO>". $incrementId ."</ONL_DOCNO><USERID>". $vendorId ."</USERID></item></HEADER>";
		return $content;
	}

	public function getDealClosingItemsContent($receipt) {
		$emp_share_content = $this->getEmployeeShareContent($receipt);
		$mm_share_content = $this->getMMShareContent($receipt);
		$vendor_share_content = $this->getVendorShareContent($receipt);
		$content = "<VENDORPAY>". $emp_share_content . $mm_share_content . $vendor_share_content."</VENDORPAY>";
		return $content;
	}

	public function getEmployeeShareContent($receipt) {
		$itemId = $receipt->getProductId();
		$receiptId = $receipt->getId();
		$collection = $this->dealClose->getCollection()->addFieldToFilter("deal_recipt_id",$receiptId);
		$content = '';
		$i = 1;
		foreach($collection as $executive){
			$exec = $this->executives->load($executive->getBusinessExecId());
			$content .= "<item><PRD_ID>". $itemId ."</PRD_ID><ITEM_ID>". $itemId ."</ITEM_ID><SR_NO>". $i ."</SR_NO><PAY_TYPE></PAY_TYPE><LIFNR>". $exec->getCode() ."</LIFNR><KBETR_P>". $executive->getPercentage() ."</KBETR_P><KBETR_A></KBETR_A><PERNR>". $exec->getEmpPersonalNumber() ."</PERNR><WRBTR></WRBTR><FROMDATE></FROMDATE><TODATE></TODATE></item>";
			$i++;
		}
		return $content;
	}

	public function getMMShareContent($receipt) {
		$itemId = $receipt->getProductId();
		$mmshare = $receipt->getMmShare();
		if($mmshare < 0)return;
		$receiptId = $receipt->getId();
		$collection = $this->itemCollectionFactory->create();		
		$collection->addFieldToFilter("product_id", $itemId);
		$collection->getSelect()->columns(['total_amount' => new \Zend_Db_Expr('SUM(row_total)'),'total_discount' => new \Zend_Db_Expr('SUM(discount_amount)')])->group('product_id');
		$collection->addFieldToFilter("deal_recipt_id",$receiptId);
		//echo $collection->getSelect();die;
		$total_amount = $collection->getFirstItem()->getTotalAmount();
		$total_discount = $collection->getFirstItem()->getTotalDiscount();
		$total = $total_amount - $total_discount;
		$amount = ($total * $mmshare)/100;
		$amount = round($amount,2);
		$i = 1;
		$content = "<item><PRD_ID>". $itemId ."</PRD_ID><ITEM_ID>". $itemId ."</ITEM_ID><SR_NO>". $i ."</SR_NO><PAY_TYPE></PAY_TYPE><LIFNR>MMCL</LIFNR><KBETR_P></KBETR_P><KBETR_A></KBETR_A><PERNR></PERNR><WRBTR>". $amount ."</WRBTR><FROMDATE></FROMDATE><TODATE></TODATE></item>";
		return $content;
	}

	public function getVendorShareContent($receipt) {
		$itemId = $receipt->getProductId();
		$mmshare = $receipt->getMmShare();
		$vendorshare = 100 - $receipt->getMmShare();
		if($vendorshare < 0)return;
		$receiptId = $receipt->getId();
		$vendorId = $receipt->getVendorId();
		$merchant = $this->merchantModel->getMerchant($vendorId);
		$name = $merchant->getContactPerson();
		$collection = $this->itemCollectionFactory->create();		
		$collection->addFieldToFilter("product_id", $itemId);
		$collection->getSelect()->columns(['total_amount' => new \Zend_Db_Expr('SUM(row_total)'),'total_discount' => new \Zend_Db_Expr('SUM(discount_amount)')])->group('product_id');
		$collection->addFieldToFilter("deal_recipt_id",$receiptId);
		//echo $collection->getSelect();die;
		$total_amount = $collection->getFirstItem()->getTotalAmount();
		$total_discount = $collection->getFirstItem()->getTotalDiscount();
		$total = $total_amount - $total_discount;
		$amount = ($total * $vendorshare)/100;
		$amount = round($amount,2);
		$i = 1;
		$content = "<item><PRD_ID>". $itemId ."</PRD_ID><ITEM_ID>". $itemId ."</ITEM_ID><SR_NO>". $i ."</SR_NO><PAY_TYPE>N</PAY_TYPE><LIFNR>". $name ."</LIFNR><KBETR_P></KBETR_P><KBETR_A></KBETR_A><PERNR></PERNR><WRBTR>". $amount ."</WRBTR><FROMDATE></FROMDATE><TODATE></TODATE></item>";
		return $content;
	}	

	public function getStatusContent($receipt) {
        $content = "<ONLORDSTATUS><item><ONL_DOCNO></ONL_DOCNO><MTTID></MTTID><ITEM_ID></ITEM_ID><WRBTR></WRBTR><USERID></USERID><STATUS></STATUS><MESSAGE></MESSAGE></item></ONLORDSTATUS>";		
		 return $content;
	}

	public function getValidDateContent($receipt) { 
         $content = "<ONLVALIDATESTAT><item><ONL_DOCNO></ONL_DOCNO><ITEM_ID></ITEM_ID><USERID></USERID><STATUS></STATUS><MESSAGE></MESSAGE></item></ONLVALIDATESTAT>";		
		 return $content;
	
	}
}
