<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wcb\ApiConnect\Model;

use Magento\Framework\Model\AbstractModel;
use Wcb\ApiConnect\Api\Data\SoapClientInterface;

class SoapClient extends AbstractModel implements SoapClientInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
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

    public function GetItemAvailabilityOnLocation($itemNo=null){
        $soapUrl = "http://172.30.54.201:7047/WurthHRV_Test/WS/WURTH_HRVATSKA%20Test/Codeunit/ShopSync?wsdl";

        $soapUser = "WHRINDIA";  //  username

        $soapPassword = "cUE48c0X"; // password

        // xml post structure

        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:shop="urn:microsoft-dynamics-schemas/codeunit/ShopSync">
    <soapenv:Header/>
    <soapenv:Body>
    <shop:GetItemEShopSalesPriceAndDisc>
    <shop:customerNoP>110508</shop:customerNoP>
    <shop:itemNoP>'.$itemNo.'</shop:itemNoP>
    <shop:qtyOnSalesLineAsTxtP>1</shop:qtyOnSalesLineAsTxtP>
    <shop:suggestedPriceAsTxtP>?</shop:suggestedPriceAsTxtP>
    <shop:suggestedDiscountAsTxtP>?</shop:suggestedDiscountAsTxtP>
    <shop:suggestedSalesPriceInclDiscAsTxtP>?</shop:suggestedSalesPriceInclDiscAsTxtP>
    <shop:suggestedPriceTypeP>?</shop:suggestedPriceTypeP>
    <shop:regularPriceAsTxtP>?</shop:regularPriceAsTxtP>
    <shop:regularDiscountAsTxtP>?</shop:regularDiscountAsTxtP>
    <shop:tAPriceLCYAsTxtP>?</shop:tAPriceLCYAsTxtP>
    <shop:tADiscountAsTxtP>?</shop:tADiscountAsTxtP>
    <shop:campaignPriceAsTxtP>?</shop:campaignPriceAsTxtP>
    <shop:oVSPriceLCYAsTxtP>?</shop:oVSPriceLCYAsTxtP>
    <shop:oVSDiscountAsTxtP>?</shop:oVSDiscountAsTxtP>
    <shop:noteP>?</shop:noteP>
    </shop:GetItemEShopSalesPriceAndDisc>
    </soapenv:Body>
    </soapenv:Envelope>';   // data from the form, e.g. some ID number

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: urn:microsoft-dynamics-schemas/codeunit/ShopSync:GetItemAvailabilityOnLocation",
            "Content-length: ".strlen($xml_post_string),
        ); //SOAPAction: your op URL

        $url = $soapUrl;

        // PHP cURL  for https connection with auth
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc

        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);

        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);

        print_r($response);

    }
}

