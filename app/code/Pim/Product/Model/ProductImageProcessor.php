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
class ProductImageProcessor
{
    /**
     * @var \Magento\Indexer\Model\IndexerFactory
     */
    protected $indexerFactory;

    protected $imageId;

    protected $product;


    public function __construct(
        \Magento\Store\Model\StoreManagerInterface              $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface         $productRepository,
        \Magento\Catalog\Model\ProductFactory                   $productFactory,
        \Pim\Product\Model\ProductImagesFactory                 $productImagesFactory,
        \Pim\Product\Model\ResourceModel\ProductImages          $productImagesResource,
        LoggerInterface                                         $logger,
        \Magento\Indexer\Model\IndexerFactory                   $indexerFactory,
        \Pim\Product\Model\ImportImageService                   $importImageService,
        \Magento\Framework\Indexer\IndexerRegistry              $indexReg,
        \Magento\Framework\Indexer\ConfigInterface              $config



    ) {

        $this->storeManager = $storeManager;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->indexerFactory = $indexerFactory;
        $this->productImagesFactory = $productImagesFactory;
        $this->importImageService = $importImageService;
        $this->productImagesResource = $productImagesResource;
        $this->indexReg = $indexReg;
        $this->config = $config;


    }

    /**
     * @param array $fixtures
     * @throws \Exception
     */
    public function install()
    {
        $this->product = $this->productFactory->create();
        $productCollection = $this->product->getCollection();
        $indexLists = ['catalog_category_product', 'catalog_product_category', 'catalog_product_attribute'];
       
      
        if ($productCollection->getSize() && $productCollection->count()) {
            $i = 0;
            foreach ($productCollection as $product) {
                $time_start = microtime(true); 
                $sku = $product->getSku();
                $productImagesFactoryObject = $this->productImagesFactory->create();
                $productImagesObject = $productImagesFactoryObject->getCollection()
                    ->addFieldToFilter('ProductId', $sku);
              
                if ($productImagesObject->getSize() && $productImagesObject->count()) {
                    foreach ($productImagesObject as $images) {
                        
                        try {
                            $imageUrl = $images->getData('Path');
                            if ($imageUrl) {

                            $this->importImageService->execute($product, $imageUrl, $visible = true, $imageType = ['image']);
                            }
                            $ThumbnailPath = $images->getData('ThumbnailPath');
                             if ($ThumbnailPath) {
                               $this->importImageService->execute($product, $ThumbnailPath, $visible = true, $imageType = ['small_image', 'thumbnail']);
                             }
                            $id = $images->getId();
                            //print_r($images->getData());
                            //echo $id;
                            $this->productImagesResource->updateByQuery($id);
                        } catch (\Exception $e) {
                            $this->logger->info(print_r($e->getMessage(), true));
                        }
                    }
                    $this->productRepository->save($product);
                    $productImagePath = $product->getData('image');
                    if ($product->getData('image') && $imageUrl) {
                        $time_end = microtime(true);
                        $log  = "Product Sku : " . $sku . " Image Path " . $productImagePath . PHP_EOL;
                        $log  .= "Process took ". number_format(microtime(true) - $time_start, 2). " seconds.".PHP_EOL;
                        $log  .= "Download images from ".$imageUrl. " Done".PHP_EOL;
                        $this->getImageImportLogger($log);
                        echo $log;
                    }
                }
          
               if ($i == 500) {
                    $i=0;
					$this->reindexByKey($indexLists);
                }
                $i++;
            }
        }
    }

    public function getImageImportLogger($log)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/image_import.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($log);
    }

 

    /**
     * Regenerate all index
     *
     * @return void
     * @throws \Exception
     */
    private function reindexByKey($indexLists){
        echo 'Full Reindex started .....'.PHP_EOL;
        foreach ($indexLists as $indexerId) {
            $indexer = $this->indexerFactory->create()->load($indexerId);
            $indexer->reindexAll();
        }
        echo 'Full Reindex Done.'.PHP_EOL;;
    }


  
}
