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

	public function execute()
	{ 
		$skus = $this->getRequest()->getParam('skus');
		
		$sku = "";
		$data = "";
		$dataString = "";
		$key = "";

	
		foreach($skus as $key=>$sku){
			$dataString .= $sku['0'].';'.$sku['1'].PHP_EOL; 
			
		}
		$dataString = trim($dataString);
        //echo $dataString;exit;
		 /** @var \Magento\Framework\Controller\Result\Json $result */
		 $result = $this->resultJsonFactory->create();
		
		 //$sku = $this->getRequest()->getPost('sku');
		 $xmlStock = $this->getSingleStock($dataString);
		 
		 $xmlStock = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $xmlStock);
         $data = simplexml_load_string($xmlStock);
		 //echo $data;
		 $result->setData(array('success'=>$data));
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
		
	public function getSingleStock($sku='899 102310'){
		
		return $this->_soapApiClient->GetMultiItemAvailabilityOnLocation($sku);



	}
}