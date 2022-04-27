<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare (strict_types = 1);

namespace Wcb\ApiConnect\Model;

use Magento\Framework\Model\AbstractModel;
use Wcb\ApiConnect\Api\Data\SoapClientInterface;

class SoapClient extends AbstractModel implements SoapClientInterface
{

    const XML_PATH_SOAP_USER = 'soap_api_setting/config/api_user';
    const XML_PATH_SOAP_PASSWORD = 'soap_api_setting/config/api_password';
    const XML_PATH_SOAP_URL = 'soap_api_setting/config/api_url';

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {

        $this->scopeConfig = $scopeConfig;

    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(\Wcb\ApiConnect\Model\ResourceModel\SoapClient::class);
    }

    /**
     * @inheritDoc
     */
    public function getSoapclientId()
    {
        return $this->_get(self::SOAPCLIENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setSoapclientId($soapclientId)
    {
        return $this->setData(self::SOAPCLIENT_ID, $soapclientId);
    }

    /**
     * @inheritDoc
     */
    public function getContent()
    {
        return $this->_get(self::CONTENT);
    }

    /**
     * @inheritDoc
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }


    public function getSoapUser()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        return $this->scopeConfig->getValue(self::XML_PATH_SOAP_USER, $storeScope);
    }


    public function getSoapPassword()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        return $this->scopeConfig->getValue(self::XML_PATH_SOAP_PASSWORD, $storeScope);

    }

    public function getSoapUrl()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        return $this->scopeConfig->getValue(self::XML_PATH_SOAP_URL, $storeScope);

    }


    public function GetMultiItemAvailabilityOnLocation($itemNo = null)
    {

        // xml post structure

        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:shop="urn:microsoft-dynamics-schemas/codeunit/ShopSync">';
        $xml_post_string .= '<soapenv:Header/>';
        $xml_post_string .= '<soapenv:Body>';
        $xml_post_string .= '<shop:GetMultiItemAvailabilityOnLocation>';
        $xml_post_string .= '<shop:userIdP>djordje</shop:userIdP>';
        $xml_post_string .= '<shop:locationCodeP>100</shop:locationCodeP>';
        $xml_post_string .= '<shop:itemsCsvP>';
        $xml_post_string .= $itemNo;
        $xml_post_string .= '</shop:itemsCsvP>';
        $xml_post_string .= '</shop:GetMultiItemAvailabilityOnLocation>';
        $xml_post_string .= '</soapenv:Body>';
        $xml_post_string .= '</soapenv:Envelope>';
        $xml_post_string = trim($xml_post_string);
        // data from the form, e.g. some ID number
        $response = $this->initCurl($xml_post_string);

        return $response;

    }

    public function GetMultiItemEShopSalesPriceAndDisc($itemNo = null)
    {

        // xml post structure

        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:shop="urn:microsoft-dynamics-schemas/codeunit/ShopSync">';
        $xml_post_string .= '<soapenv:Header/>';
        $xml_post_string .= '<soapenv:Body>';
        $xml_post_string .= '<shop:GetMultiItemEShopSalesPriceAndDisc>';
        $xml_post_string .= '<shop:customerNoP>110508</shop:customerNoP>';
        $xml_post_string .= '<shop:salesLinesCsvP>';
        $xml_post_string .= $itemNo;
        $xml_post_string .= '</shop:salesLinesCsvP>';
        $xml_post_string .= '</shop:GetMultiItemEShopSalesPriceAndDisc>';
        $xml_post_string .= '</soapenv:Body>';
        $xml_post_string .= '</soapenv:Envelope>';
        // data from the form, e.g. some ID number
        $response = $this->initCurl($xml_post_string);

        return $response;

    }

    public function GetItemEShopSalesPriceAndDisc($itemNo = null)
    {

        // xml post structure


        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:shop="urn:microsoft-dynamics-schemas/codeunit/ShopSync">';
        $xml_post_string .= '<soapenv:Header/>';
        $xml_post_string .= '<soapenv:Body>';
        $xml_post_string .= '<shop:GetItemEShopSalesPriceAndDisc>';
        $xml_post_string .= '<shop:customerNoP>110508</shop:customerNoP>';
        $xml_post_string .= '<shop:itemNoP>' . $itemNo . '</shop:itemNoP>';
        $xml_post_string .= '<shop:qtyOnSalesLineAsTxtP>1</shop:qtyOnSalesLineAsTxtP>';
        $xml_post_string .= '<shop:suggestedPriceAsTxtP>?</shop:suggestedPriceAsTxtP>';
        $xml_post_string .= '<shop:suggestedDiscountAsTxtP>?</shop:suggestedDiscountAsTxtP>';
        $xml_post_string .= '<shop:suggestedSalesPriceInclDiscAsTxtP>?</shop:suggestedSalesPriceInclDiscAsTxtP>';
        $xml_post_string .= '<shop:suggestedPriceTypeP>?</shop:suggestedPriceTypeP>';
        $xml_post_string .= '<shop:regularPriceAsTxtP>?</shop:regularPriceAsTxtP>';
        $xml_post_string .= '<shop:regularDiscountAsTxtP>?</shop:regularDiscountAsTxtP>';
        $xml_post_string .= '<shop:tAPriceLCYAsTxtP>?</shop:tAPriceLCYAsTxtP>';
        $xml_post_string .= '<shop:tADiscountAsTxtP>?</shop:tADiscountAsTxtP>';
        $xml_post_string .= '<shop:campaignPriceAsTxtP>?</shop:campaignPriceAsTxtP>';
        $xml_post_string .= '<shop:oVSPriceLCYAsTxtP>?</shop:oVSPriceLCYAsTxtP>';
        $xml_post_string .= '<shop:oVSDiscountAsTxtP>?</shop:oVSDiscountAsTxtP>';
        $xml_post_string .= '<shop:noteP>?</shop:noteP>';
        $xml_post_string .= '</shop:GetItemEShopSalesPriceAndDisc>';
        $xml_post_string .= '</soapenv:Body>';
        $xml_post_string .= '</soapenv:Envelope>'; // data from the form, e.g. some ID number
        $response = $this->initCurl($xml_post_string);

        return $response;

    }

    public function GetItemAvailabilityOnLocation($itemNo = null)
    {  //echo $itemNo;exit;

        // xml post structure
       /* $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:shop="urn:microsoft-dynamics-schemas/codeunit/ShopSync">
        <soapenv:Header/>
        <soapenv:Body>
           <shop:GetItemAvailabilityOnLocation>
              <shop:itemNoP>' . $itemNo . '</shop:itemNoP>
              <shop:locationCodeP>100</shop:locationCodeP>
              <shop:availableQtyAsTxtP>10</shop:availableQtyAsTxtP>
              <shop:itemDefaultVendorNoP>800001</shop:itemDefaultVendorNoP>
           </shop:GetItemAvailabilityOnLocation>
        </soapenv:Body>
     </soapenv:Envelope>';*/ // data from the form, e.g. some ID number
     
$xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:shop="urn:microsoft-dynamics-schemas/codeunit/ShopSync">';
$xml_post_string .= '<soapenv:Header/>';
$xml_post_string .= '<soapenv:Body>';
$xml_post_string .= '<shop:GetItemAvailabilityOnLocationEShop>';
$xml_post_string .= '<shop:itemNoP>' . $itemNo . '</shop:itemNoP>';
$xml_post_string .= '<shop:locationCodeP>100</shop:locationCodeP>';
$xml_post_string .= '<shop:requestedQtyAsTxtP>1</shop:requestedQtyAsTxtP>';
$xml_post_string .= '<shop:availableQtyAsTxtP>?</shop:availableQtyAsTxtP>';
$xml_post_string .= '<shop:userIdP>?</shop:userIdP>';
$xml_post_string .= '<shop:availabilityStatusP>?</shop:availabilityStatusP>';
$xml_post_string .= '<shop:availabilityOnDateP>?</shop:availabilityOnDateP>';
$xml_post_string .= '</shop:GetItemAvailabilityOnLocationEShop>';
$xml_post_string .= '</soapenv:Body>';
$xml_post_string .= '</soapenv:Envelope>';
//echo trim($xml_post_string);exit;
        $response = $this->initCurl($xml_post_string);
//print_r($response);exit;
        return $response;

    }

    public function initCurl($xml_post_string)
    {

        $soapUrl = $this->getSoapUrl();

        $soapUser = $this->getSoapUser(); //  username

        $soapPassword = $this->getSoapPassword(); // password
        // PHP cURL  for https connection with auth

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: urn:microsoft-dynamics-schemas/codeunit/ShopSync:GetItemAvailabilityOnLocation",
            "Content-length: " . strlen($xml_post_string),
        ); //SOAPAction: your op URL

        $url = $soapUrl;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_USERPWD, $soapUser . ":" . $soapPassword); // username and password - declared at the top of the doc

        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);

        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);
        $response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
		$response = simplexml_load_string($response);
        //print_r($response);exit;
        return $response;

    }

    public function trimMiddleWhiteSpaces($header){
		$newHeader = [];
		foreach($header as $value){
			$newHeader[] = preg_replace( '/[^A-Za-z0-9\-]/', '',$value);

		}

		return $newHeader;

	}

  public function csvstring_to_array($string, $separatorChar = ',', $enclosureChar = '"', $newlineChar = "\n") {
    // @author: Klemen Nagode
    $array = array();
    $size = strlen($string);
    $columnIndex = 0;
    $rowIndex = 0;
    $tempChar="";
    $fieldValue="";
    $isEnclosured = false;
    for($i=0; $i<$size;$i++) {

        $char = (string) $string[$i];
        $addChar = "";

        if($isEnclosured) {
            if($char==$enclosureChar) {
                $tempChar = (string) $string[$i+1];
                if($i+1<$size && $tempChar==$enclosureChar){
                    // escaped char
                    $addChar=$char;
                    $i++; // dont check next char
                }else{
                    $isEnclosured = false;
                }
            }else {
                $addChar=$char;
            }
        }else {
            if($char==$enclosureChar) {
                $isEnclosured = true;
            }else {

                if($char==$separatorChar) {

                    $array[$rowIndex][$columnIndex] = $fieldValue;
                    $fieldValue="";

                    $columnIndex++;
                }elseif($char==$newlineChar) {
                    echo $char;
                    $array[$rowIndex][$columnIndex] = $fieldValue;
                    $fieldValue="";
                    $columnIndex=0;
                    $rowIndex++;
                }else {
                    $addChar=$char;
                }
            }
        }
        if($addChar!=""){
            $fieldValue.=$addChar;

        }
    }

    if($fieldValue) { // save last field
        $array[$rowIndex][$columnIndex] = $fieldValue;
    }
    return $array;
}

}
