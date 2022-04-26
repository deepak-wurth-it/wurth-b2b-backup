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
    CONST PRICE_INDEXER_ID = 'catalog_product_price';
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

        $objPimBarCodeProduct = $this->productBarCodeFactory->create();
        $connectionPimBarCode = $objPimBarCodeProduct->getResource()->getConnection();

        $collectionPimBarCode = $objPimBarCodeProduct->getCollection()
          ->addFieldToFilter('Active', ['eq' => '1'])
          ->addFieldToFilter('UpdateRequired', ['eq' => '1']);
          //echo $collectionPimBarCode->getSelect();exit;
          

        $x = 0;
        if ($collectionPimBarCode->getSize() && $collectionPimBarCode->count()) {

            foreach ($collectionPimBarCode as $item) {
				

                try {

					$sku = $item->getData('ProductId');
					$code = $item->getData('Code');
					if(empty($sku) && empty($code)){
						continue;
				    }
                    $this->product =$this->productRepository->get($sku);
                    
                    
                    if ($this->product->getId() && $sku && $code) {

                        $this->product->setData('product_bar_code',$code);
					try {
							
							$this->productRepository->save($this->product);
                            $log = 'Updated Product Bar Code of sku'.$sku.PHP_EOL;
                            $item->setData('UpdateRequired','0');
                            $item->save();

                        } catch (\Exception $e) {
                            echo $e->getMessage().PHP_EOL;
                            $this->logger->info('Error Updating Product Bar Code of sku: ' . $sku . '. ' . $e->getMessage());
                            continue;
                        }
                        

                    }
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
                $x++;
                if ($x == 500) {
                    $x=0;
                
                $this->reindexByKey($indexLists);

                    //break;
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
    
   public function getBarCodeLogger($log)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/product_barcode.log');
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

