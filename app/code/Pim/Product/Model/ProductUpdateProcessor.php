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
class ProductUpdateProcessor
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
        \Magento\Framework\Indexer\ConfigInterface $config


    )
    {

        $this->storeManager = $storeManager;
        $this->pimProductFactory = $pimProductFactory;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
        $this->pimProductsCategoriesFactory = $pimProductsCategoriesFactory;
        $this->logger = $logger;
        $this->indexerFactory = $indexerFactory;
        $this->config = $config;
    }

    /**
     * @param array $fixtures
     * @throws \Exception
     */
    public function install()
    {   
        $this->product = '';
        $objPimProduct = $this->pimProductFactory->create();
        
        $collection = $objPimProduct->getCollection()
            ->addFieldToFilter('Status', ['eq' => '1'])
            ->addFieldToFilter('magento_sync_status', [['eq' => '1']]);
           
        $x = 0;
        if ($collection->getSize() && $collection->count()) {

            foreach ($collection as $item) {

                //echo $collection->getSize();exit;
                 $sku = $item->getData('Id');
                 
                 $productObj =$this->productFactory->create();
					if(!$productObj->getIdBySku($sku)) {
						continue;   
				 }
                 
                
                $this->product = $this->productFactory->create();
                $this->product->load($this->product->getIdBySku($sku));

                try {

                    $pimProductCode = $this->getPimProductCode($item);
                    echo 'Start Product sku '.$pimProductCode.PHP_EOL;


                    if ($pimProductCode ) {
                        $this->product->setProductCode($pimProductCode);
                      try {
                            $this->product->save();
                            echo 'End Product Id Update'.$pimProductCode.PHP_EOL;


                        } catch (\Exception $e) {
                            echo $e->getMessage().PHP_EOL;
                            $this->logger->info('Error importing product sku: ' . $pimProductCode . '. ' . $e->getMessage());
                            continue;
                        }

                    }
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
                $x++;
                if ($x == 10000) {

                    $x=0;
                    $this->reindexAll();
                    //break;
                }
            }

        }
    }

   

    public function setProductCode($item)
    {
        if ($this->product && $item) {
            $pimProductCode = $item->getData('Code') ? $item->getData('Code') : '';
            $this->product->setProductCode($pimProductCode);
        }

    }

    

    public function getPimProductCode($item)
    {
        $pimProductCode = '';
        if ($item) {

            $pimProductCode
             = $item->getData('Code') ? $item->getData('Code') : '';

        }
        return $pimProductCode;

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

}

