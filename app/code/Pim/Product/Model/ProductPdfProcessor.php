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
class ProductPdfProcessor extends \Magento\Framework\DataObject
{


    /**
     * @var \Magento\Indexer\Model\IndexerFactory
     */
    protected $indexerFactory;

    const PDF_SEPRATOR = '||';

    /**
     * @var \Magento\Framework\Indexer\ConfigInterface
     */
    protected $config;

    protected $product;
    
    protected $pdfRow;

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
        \Pim\Product\Model\ImportPdfService    $importPdfService,
        \Magento\Framework\Escaper $escaper,
        \Wcb\Catalog\Model\ResourceModel\ProductPdfFactory $resourceModelPdfFactory,
         \Wcb\Catalog\Model\ProductPdfFactory $modelPdfFactory

    ) {

        $this->storeManager = $storeManager;
        $this->escaper = $escaper;
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
        $this->modelPdfFactory = $modelPdfFactory;
        $this->resourceModelPdfFactory = $resourceModelPdfFactory;
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
        $x = 0;
        if ($collectionPimPdf->getSize() && $collectionPimPdf->count()) {

            foreach ($collectionPimPdf as $item) {
				$this->pdfRow = "";
				$this->product = "";
                try {

                    $sku = $item->getData('ProductId');
                   
                    $productObj = $this->productFactory->create();
                    if (!$productObj->getIdBySku($sku)) {
                        continue;
                    }

                    $pdfUrl = $item->getData('pdf_path');
                    $pdf_name = $item->getData('name');
                   	$pdf_id = $item->getData('PdfId');

					//echo $pdfUrl.PHP_EOL;
					//echo $pdf_name.PHP_EOL;exit;
                    $objProduct = $this->productFactory->create();

                    $this->product = $objProduct->load($objProduct->getIdBySku($sku));

                    if ($sku && $this->product->getId() && $pdfUrl) {

                        try {
							
							$this->pdfRow = $this->modelPdfFactory->create();
							$existRowObject = $this->pdfRow->load($pdf_id,'pdf_id');
							
							$IsMainPdf = $item->getData('IsMainPdf');
							$product_id =  $this->product->getId();
							$pdf_type_id = $item->getData('pdf_type_id');
							$Active = $item->getData('Active');
							$sku =  $this->product->getSku();
							$pdf_name = $item->getData('pdf_name');
							$ExternalId = $item->getData('ExternalId');
							
							if ($existRowObject && is_object($existRowObject) && $existRowObject->getId()) {
								
								$this->pdfRow = $existRowObject;
								$log =  'Product PDF  Updated For Sku =>>'.$sku.PHP_EOL;
							}else{
								$log =  'Product PDF  Added For Sku =>>'.$sku.PHP_EOL;
							}
							

							
							$uploadedPdf = $this->importPdfService->execute($pdf_name, $pdfUrl);
							if($uploadedPdf){
								
								$this->pdfRow->setData('is_main_pdf',$IsMainPdf);
								
								
								$this->pdfRow->setData('product_id',$product_id);
								
								
								$this->pdfRow->setData('pdf_type_id',$pdf_type_id);
								
								
								$this->pdfRow->setData('pdf_active_status',$Active);
								
								
								$this->pdfRow->setData('sku',$sku);
								
								
								$this->pdfRow->setData('pdf_name',$pdf_name);
								
								$this->pdfRow->setData('pdf_id',$pdf_id);
								
								$this->pdfRow->setData('pdf_url',$uploadedPdf);
								
								$this->pdfRow->setData('external_id',$ExternalId);
								
								
								$this->pdfRow->setData($ExternalId);
								
								$this->pdfRow->save();
								$log .= 'End Product PDF ,Product Sku '.$sku.PHP_EOL;
								//echo 'rwerwsdfdsfdsfdsfsdfssderw';exit;
							}
							//Remain pdf end

                            if($IsMainPdf == 0 || $IsMainPdf==1){
								$log = 'Updated Product Pdf of sku ' . $sku . PHP_EOL;
								$item->setData('UpdateRequired', 1);
								$item->save();
						   }
                        } catch (\Exception $e) {
                            echo $e->getMessage() . PHP_EOL;
                            $this->logger->info('Error Updating Product Pdf of sku: ' . $sku . '. ' . $e->getMessage());
                            continue;
                        }

                        $x++;
                    }
                } catch (Exception $e) {
                    echo $e->getMessage();
                }

                //if ($x == 500) {
                //    $x = 0;
                //
                //    $this->reindexByKey($indexLists);
                //
                    //break;
               // }
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
    private function reindexOne($indexId)
    {
        $indexer = $this->indexerFactory->create()->load($indexId);
        $indexer->reindexAll();
    }
    /**
     * Regenerate all index
     *
     * @return void
     * @throws \Exception
     */
    private function reindexAll()
    {
        echo 'Full Reindex started .....' . PHP_EOL;
        foreach (array_keys($this->config->getIndexers()) as $indexerId) {
            $indexer = $this->indexerFactory->create()->load($indexerId);
            $indexer->reindexAll();
        }
        echo 'Full Reindex Done.' . PHP_EOL;;
    }

    public function getProductImportPdfLogger($log)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/product_pdf.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($log);
    }


    private function reindexByKey($indexLists)
    {
        echo 'Full Reindex started .....' . PHP_EOL;
        foreach ($indexLists as $indexerId) {
            $indexer = $this->indexerFactory->create()->load($indexerId);
            $indexer->reindexAll();
        }
        echo 'Full Reindex Done.' . PHP_EOL;;
    }

 }
