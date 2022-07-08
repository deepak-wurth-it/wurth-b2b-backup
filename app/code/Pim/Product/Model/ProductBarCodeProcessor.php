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
class ProductBarCodeProcessor
{
    const PRICE_INDEXER_ID = 'catalog_product_price';
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
        \Pim\Product\Model\ProductBarCodeFactory $productBarCodeFactory



    ) {

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


        $this->config = $config;
    }

    /**
     * @param array $fixtures
     * @throws \Exception
     */
    public function install()
    {
        $log = '';
        $codes = [];
        $this->product = '';
        $indexLists = ['catalog_category_product', 'catalog_product_category', 'catalog_product_attribute'];

        $this->product = '';
        $magePro = $this->productFactory->create();

        $collection = $magePro->getCollection()->addAttributeToSelect('*');

        $x = 0;
        if ($collection->getSize() && $collection->count()) {

            foreach ($collection as $productObj) {              
                try {
                    $sku = $productObj->getSku();
                    $barCodes = $this->productBarCodeFactory->create()->getCollection()
                        //->addFieldToSelect("Code")
                        ->addFieldToFilter('ProductId', ['in' => '22'])
                        ->addFieldToFilter('Active', [['eq' => '1']])
                        ->addFieldToFilter('UpdateRequired', [['eq' => '1']]);

                    if ($barCodes->getSize() < 1) {
                        continue;
                    }

                    $this->product = $this->productRepository->get($sku);

                    if ($this->product->getId() && $barCodes->getSize() &&  $barCodes->count()) {


                        try {
                            $code = implode('|', array_column($barCodes->getData(), 'Code'));

                            $this->product->setData('product_bar_code', $code);
                            $this->productRepository->save($this->product);
                            $log = 'Updated Product Bar Code of sku' . $sku . PHP_EOL;

                            foreach ($barCodes as $code) {
                                $code->setData('UpdateRequired', '0');
                                $code->save();
                            }
                        } catch (\Exception $e) {
                            echo $e->getMessage() . PHP_EOL;
                            $this->logger->info('Error Updating Product Bar Code of sku: ' . $sku . '. ' . $e->getMessage());
                            continue;
                        }
                    }
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
                $x++;
                if ($x == 500) {
                    $x = 0;


                    $this->reindexByKey($indexLists);

                    break;
                }
                echo  $log;
                $this->getBarCodeLogger($log);
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

    public function getBarCodeLogger($log)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/product_barcode.log');
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
