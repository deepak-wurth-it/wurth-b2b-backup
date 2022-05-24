<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Pim\Product\Model;
use Psr\Log\LoggerInterface;


/**
 * Setup sample attributes
 *
 * Class Attribute
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductPdfProcessor
{
    /**
     * @var \Magento\Indexer\Model\IndexerFactory
     */
    protected $indexerFactory;

    /**
     * @var \Magento\Framework\Indexer\ConfigInterface
     */
    protected $config;

    protected $product;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param ProductFactory $pimProductFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Pim\Category\Model\PimProductsCategoriesFactory $pimProductsCategoriesFactory
     * @param LoggerInterface $logger
     * @param \Magento\Indexer\Model\IndexerFactory $indexerFactory
     * @param \Magento\Framework\Indexer\ConfigInterface $config
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface           $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface      $productRepository,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Pim\Product\Model\ProductFactory                    $pimProductFactory,
        \Magento\Catalog\Model\ProductFactory                $productFactory,
        \Pim\Category\Model\PimProductsCategoriesFactory     $pimProductsCategoriesFactory,
        LoggerInterface $logger,
        \Magento\Indexer\Model\IndexerFactory $indexerFactory,
        \Magento\Framework\Indexer\ConfigInterface $config,
        \Pim\Product\Model\ProductPdfFactory $productPdfFactory,
        \Pim\Product\Model\ProductBarCodeFactory $productBarCodeFactory,
        \Pim\Product\Model\ImportPdfService    $importPdfService




    )
    {

        $this->storeManager = $storeManager;
        $this->pimProductFactory = $pimProductFactory;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->stockRectsCategoriesFagistry = $stockRegistry;
        $this->pimProductsCategoriesFactory = $pimProductsCategoriesFactory;
        $this->logger = $logger;
        $this->indexerFactory = $indexerFactory;
        $this->productPdfFactory = $productPdfFactory;
        $this->productBarCodeFactory = $productBarCodeFactory;
        $this->importPdfService = $importPdfService;



        $this->config = $config;
    }

    /**
     * @param array $fixtures
     * @throws \Exception
     */
    public function install()
    {
		$log = '';
        $this->product = '';
        $indexLists = ['catalog_category_product', 'catalog_product_category', 'catalog_product_attribute'];

        $objPimPdfProduct = $this->productPdfFactory->create();
        $connectionPimPdf = $objPimPdfProduct->getResource()->getConnection();

        $collectionPimPdf = $objPimPdfProduct->getCollection();
		echo $collectionPimPdf->getSize();
        $x = 0;
        if ($collectionPimPdf->getSize() && $collectionPimPdf->count()) {

            foreach ($collectionPimPdf as $item) {
			try {
				
			    $sku = $item->getData('ProductId');
				$productObj =$this->productFactory->create();
				if(!$productObj->getIdBySku($sku)) {
					continue;   
				}
				print_r($item->getData());
				$pdfUrl = $item->getData('pdf_path');
				$pdf_name = $item->getData('Name');
				$rest_pdf = [];
				
				 $objProduct= $this->productFactory->create();

				 $this->product = $objProduct->load($objProduct->getIdBySku($sku));
               
				if ($sku && $this->product->getId() && $pdfUrl) {
							
					try {
							$IsMainPdf =  $item->getData('IsMainPdf');
							if($IsMainPdf){
							   $pdfUrl = $this->importPdfService->execute($pdf_name,$pdfUrl);
                               $this->product->setData('product_pdf_path',$pdfUrl);
                            }
                            
                            if(empty($IsMainPdf)){
							
						       $pdfUrl = $item->getData('pdf_path');
						       $pdfUrl = $this->importPdfService->execute($pdf_name,$pdfUrl);
								
						       $existPdf = $this->product->getData('rest_pdf');
						       
						      
						       if($existPdf){
								
								  $pdfArray = json_decode($existPdf);
								 
								  array_push($pdfArray,$pdfUrl); 
								  $pdfJson = json_encode($pdfArray);
                                  $this->product->setData('rest_pdf',$pdfJson);
							   }else{
								  $pdfArray[] = $pdfUrl;
								  $pdfJson = json_encode($pdfArray);
                                  $this->product->setData('rest_pdf',$pdfJson);
							   }
							   
                            }
                            $this->product->save();
                            $log ='Updated Product Pdf of sku '.$sku.PHP_EOL;
							$item->setData('UpdateRequired','1');
                            $item->save();


                        } catch (\Exception $e) {
                            echo $e->getMessage().PHP_EOL;
                            $this->logger->info('Error Updating Product Pdf of sku: ' . $sku . '. ' . $e->getMessage());
                            continue;
                        }
                       
						$x++;
                    }
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
              
                if ($x == 500) {
                    $x=0;
                
                $this->reindexByKey($indexLists);

                    //break;
                }
               echo  $log;
               $this->getProductImportPdfLogger($log);

            }

        }
    }

    

    /**
     * Regenerate single index
     *
     * @return void
     * @throws \Exception
     */
    private function reindexOne($indexId){
        $indexer = $this->indexerFactory->create()->load($indexId);
        $indexer->reindexAll();
    }
    /**
     * Regenerate all index
     *
     * @return void
     * @throws \Exception
     */
    private function reindexAll(){
        echo 'Full Reindex started .....'.PHP_EOL;
        foreach (array_keys($this->config->getIndexers()) as $indexerId) {
            $indexer = $this->indexerFactory->create()->load($indexerId);
            $indexer->reindexAll();
        }
        echo 'Full Reindex Done.'.PHP_EOL;;
    }
    
   public function getProductImportPdfLogger($log)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/product_pdf.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($log);
    }
    

    private function reindexByKey($indexLists){
        echo 'Full Reindex started .....'.PHP_EOL;
        foreach ($indexLists as $indexerId) {
            $indexer = $this->indexerFactory->create()->load($indexerId);
            $indexer->reindexAll();
        }
        echo 'Full Reindex Done.'.PHP_EOL;;
    }

}

