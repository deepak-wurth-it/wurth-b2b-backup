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



    protected $product;


    public function __construct(
        \Magento\Store\Model\StoreManagerInterface           $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface      $productRepository,
        \Magento\Catalog\Model\ProductFactory                $productFactory,
        \Pim\Product\Model\ProductImagesFactory              $productImagesFactory,
        LoggerInterface                                      $logger,
        \Magento\Indexer\Model\IndexerFactory                $indexerFactory,
        \Pim\Product\Model\ImportImageService                $importImageService


    ) {

        $this->storeManager = $storeManager;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->indexerFactory = $indexerFactory;
        $this->productImagesFactory = $productImagesFactory;
        $this->importImageService = $importImageService;
    }

    /**
     * @param array $fixtures
     * @throws \Exception
     */
    public function install()
    {
        $this->product = $this->productFactory->create();
        $productCollection = $this->product->getCollection();
        $this->productRepository;


        if ($productCollection->getSize() && $productCollection->count()) {
            $i = 0;
            foreach ($productCollection as $product) {
                $sku = $product->getSku();
                $productImagesObject = $this->productImagesFactory->create()->getCollection()
                    ->addFieldToFilter('ProductId', $sku);

                if ($productImagesObject->getSize() && $productImagesObject->count()) {
                    foreach ($productImagesObject as $images) {
                        try {
                            $imageUrl = $images->getData('Path');
                            // if ($imageUrl) {

                            $this->importImageService->execute($product, $imageUrl, $visible = true, $imageType = ['image']);
                            // }
                            $ThumbnailPath = $images->getData('Path');
                            // if ($ThumbnailPath) {
                            $this->importImageService->execute($product, $ThumbnailPath, $visible = true, $imageType = ['small_image', 'thumbnail']);
                            // }
                        } catch (\Exception $e) {
                            $this->logger->info(print_r($e->getMessage(), true));
                        }
                    }
                    $this->productRepository->save($product);
                    $productImagePath = $product->getData('image');
                    if ($product->getData('image')) {
                        $this->getImageImportLogger($sku, $productImagePath);
                        echo 'Product Sku : ' . $sku . ' Image Path ' . $productImagePath . PHP_EOL;
                    }
                }

                $i++;
            }
        }
    }

    public function getImageImportLogger($productID, $imagePath)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/image_import.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('Product Sku : ' . $productID . ' Image Path ' . $imagePath);
    }
}
