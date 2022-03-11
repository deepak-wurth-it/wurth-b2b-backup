<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wcb\ApiConnect\Model;

class GetMultiProductPrice implements \Wcb\ApiConnect\Api\GetMultiProductPriceInterface
{
  protected $_pageFactory;
  protected $resultJsonFactory;
  public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Framework\View\Result\PageFactory $pageFactory,
    \Wcb\ApiConnect\Model\SoapClient $soapApiClient,
    \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
    \Magento\Framework\Serialize\Serializer\Json $json


    )
  {
    $this->_pageFactory = $pageFactory;
    $this->_soapApiClient = $soapApiClient;
    $this->resultJsonFactory = $resultJsonFactory;
    $this->json = $json;

    //return parent::__construct($context);
  }

  public function callMultiProductPrice($skus)
  {

    $sku = "";
    $data = "";
    $dataString = "";
    $key = "";
    $header = "";
    $finalData = [];


    foreach($skus as $key=>$sku){
      $dataString .= '"'.$sku['product_code'].'"'.';'.'"'.$sku['qty'].'"'.PHP_EOL;

    }


    $dataString = trim($dataString);

     /** @var \Magento\Framework\Controller\Result\Json $result */
     $result = $this->resultJsonFactory->create();

     $xmlData = $this->getMultiPrice($dataString);
      if($xmlData){

      $data = $xmlData->SoapBody->GetMultiItemEShopSalesPriceAndDisc_Result->salesLinesCsvP;
      $data = (string) $data;

      $data = $this->_soapApiClient->csvstring_to_array($data);

      $header = reset($data);
      $header = explode(';', $header[0]);

      foreach($data as $key=>$row){
        if(empty($row)){
          continue;
        }

        if($key == 1){
            $header = $this->_soapApiClient->trimMiddleWhiteSpaces($header);
        }
        $dataStage2 = explode(';', $row[0]);


        if(count($header) === count($dataStage2) && $key != 0){
            $finalData[] =  array_combine($header,$dataStage2);
        }
      }

     }
     return $finalData;
    }




  public function str_putcsv($data) {
        # Generate CSV data from array
        $fh = fopen('php://temp', 'wb'); # don't create a file, attempt
                                         # to use memory instead

        # write out the headers
        fputcsv($fh, array_keys(current($data)));

        # write out the data
        foreach ( $data as $row ) {
                fputcsv($fh, $row);
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return $csv;
}

  public function getMultiPrice($skus){

    return $this->_soapApiClient->GetMultiItemEShopSalesPriceAndDisc($skus);



  }




}
