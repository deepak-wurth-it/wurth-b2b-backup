<?php /**
 * Copyright © 2021 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wcb\Catalog\Controller\Ajax;
class GetMultiProductStock extends \Magento\Framework\App\Action\Action
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

	/*public function execute()
	{ 
		$skus = $this->getRequest()->getParam('skus');
		
		$sku = "";
		$data = "";
		$dataString = "";
		$key = "";
		$header = "";
		$finalData = [];

		$skus= json_decode($skus);
		
		foreach($skus as $key=>$sku){
			$dataString .= '"'.$sku['0'].'"'.';'.'"'.$sku['1'].'"'.PHP_EOL; 
			
		}
		$dataString = trim($dataString);
        //echo $dataString;exit;
		 /** @var \Magento\Framework\Controller\Result\Json $result */
		 //$result = $this->resultJsonFactory->create();
		
		 //$sku = $this->getRequest()->getPost('sku');
		/* $xmlData = $this->getMultiStock($dataString);
        if($xmlData){
		 
			$data = $xmlData->SoapBody->GetMultiItemAvailabilityOnLocation_Result->itemsCsvP;
			
		   	$data = preg_split("/\r\n|\n|\r/", $data[0]);
			
			   foreach($data as $key=>$row){
				if(empty($row)){
					continue;
				}   
				
				if($key == 0){
					$header = explode(';', $data[0]);
					$header =$this->_soapApiClient->trimMiddleWhiteSpaces($header);
				}
				$dataStage2 = explode(';', $row);
				
				if(count($header) == count($dataStage2) && $key !== 0 ){
					$finalData[] =  array_combine($header,$dataStage2);
				}
			}
		 }
		 $result->setData(array('success'=>$finalData));
         return $result;

		}*/


public function execute()
	{

		$skus = $this->getRequest()->getParam('skus');
		
		$sku = "";
		$data = "";
		$dataString = "";
		$key = "";
		$header = "";
		$finalData = [];

		$skus= json_decode($skus);
		
		foreach($skus as $key=>$sku){
			$dataString .= '"'.$sku['0'].'"'.';'.'"'.$sku['1'].'"'.PHP_EOL; 
			
		}

    $dataString = trim($dataString);

     $result = $this->resultJsonFactory->create();
     $xmlData = $this->getMultiStock($dataString);
      if($xmlData){

      $data = $xmlData->SoapBody->GetMultiItemAvailabilityOnLocation_Result->itemsCsvP;
      $data = (string) $data;
    
      $data = $this->_soapApiClient->csvstring_to_array($data);

      $header = reset($data);
      $header = explode(';', $header[0]);

      foreach($data as $key=>$row){
        if(empty($row)){
          continue;
        }

        if($key == 1){
            $header =$this->_soapApiClient->trimMiddleWhiteSpaces($header);
        }
        $dataStage2 = explode(';', $row[0]);


        if(count($header) === count($dataStage2) && $key != 0){
            $finalData[] =  array_combine($header,$dataStage2);
        }
      }

     }
     $result->setData(array('success'=>$finalData));
     return $result;
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
		
	public function getMultiStock($skus){
		
		return $this->_soapApiClient->GetMultiItemAvailabilityOnLocation($skus);



	}
}
