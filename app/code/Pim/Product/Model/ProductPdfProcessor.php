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

    const PDF_SEPRATOR = '||';

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
        \Pim\Product\Model\ImportPdfService    $importPdfService,
        \Magento\Framework\Escaper $escaper




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
        //echo $collectionPimPdf->getSize();
        $x = 0;
        if ($collectionPimPdf->getSize() && $collectionPimPdf->count()) {

            foreach ($collectionPimPdf as $item) {
                try {

                    $sku = $item->getData('ProductId');
                    $productObj = $this->productFactory->create();
                    if (!$productObj->getIdBySku($sku)) {
                        continue;
                    }
                   
                    $pdfUrl = $item->getData('pdf_path');
                    $pdf_name = $item->getData('Name');
                    $rest_pdf = [];

                    $objProduct = $this->productFactory->create();

                    $this->product = $objProduct->load($objProduct->getIdBySku($sku));

                    if ($sku && $this->product->getId() && $pdfUrl) {

                        try {
							//Main pdf start
							
                            $IsMainPdf =  $item->getData('IsMainPdf');
                            if ($IsMainPdf) {
                                $uploadedPdf = $this->importPdfService->execute($pdf_name, $pdfUrl);

                                if ($uploadedPdf) {
                                      $escape_url = $this->escaper->escapeHtml($uploadedPdf);
                                      $this->product->setData('product_main_pdf', $escape_url);
                                }
                                $this->product->setData('product_main_pdf', $pdfUrl);
                            }
                            
                            //Main pdf end
                            
                            
                            //Remain pdf start
                            if (empty($IsMainPdf)) {

                                $existPdf = $this->product->getData('product_remain_pdfs');
                                if ($existPdf) {
                                    $pdfUrl = $item->getData('pdf_path');
                                    $pdf_name = $item->getData('Name');
                                    $uploadedPdf = $this->importPdfService->execute($pdf_name, $pdfUrl);
                                    if ($uploadedPdf) {
                                        $uploadedPdf = $this->escaper->escapeHtml($uploadedPdf);
                                        $addedPdf = $existPdf . self::PDF_SEPRATOR . $uploadedPdf;
                                        $this->product->setData('product_remain_pdfs', $addedPdf);
                                    }
                                } else {
                                    $pdfUrl = $item->getData('pdf_path');
                                    $pdf_name = $item->getData('Name');
                                    $uploadedPdf = $this->importPdfService->execute($pdf_name, $pdfUrl);
                                    if ($uploadedPdf) {
                                        $uploadedPdf = $this->escaper->escapeHtml($uploadedPdf);
                                        $this->product->setData('product_remain_pdfs', $uploadedPdf);
                                    }
                                }
                            }
                            
                            //Remain pdf end
                            
                            
                            $this->product->save();
                            $log = 'Updated Product Pdf of sku ' . $sku . PHP_EOL;
                            $item->setData('UpdateRequired', '1');
                            $item->save();
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

                if ($x == 500) {
                    $x = 0;

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
