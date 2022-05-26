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
class ProductAttributeValueProcessor
{

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface           $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface      $productRepository,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Pim\Product\Model\ProductFactory                    $pimProductFactory,
        \Magento\Catalog\Model\ProductFactory                $productFactory,
        \Pim\Product\Model\ProductsAttributeValuesFactory    $productAttributeValuesFactory,
        \Magento\Indexer\Model\IndexerFactory                $indexerFactory,
        LoggerInterface $logger


    ) {

        $this->storeManager = $storeManager;
        $this->pimProductFactory = $pimProductFactory;
        $this->productAttributeValuesFactory = $productAttributeValuesFactory;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
        $this->indexerFactory = $indexerFactory;
        $this->logger = $logger;
    }

    /**
     * @param array $fixtures
     * @throws \Exception
     */
    public function install()
    {
        $log = '';
        $isProductedUpdated = false;
        $mageProduct = $this->productFactory->create();
        $indexLists = ['catalog_category_product', 'catalog_product_category', 'catalog_product_attribute'];
        $collection = $mageProduct->getCollection()
            ->addAttributeToFilter('status', ['eq' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED])
            ->addAttributeToFilter('update_required', ['eq' => '1']);
        if ($collection->getSize() && $collection->count()) {
            $i = 0;
            foreach ($collection as $itemProduct) {
                $sku = $itemProduct->getSku();
                $productId = $itemProduct->getId();
                if (!$sku) {
                    continue;
                }
                $collectionB = $this->productAttributeValuesFactory->create();
                $collectionB = $collectionB->getCollection();
                $collectionB->getSelect()
                    ->join(
                        array("av" => "attributevalues"),
                        "main_table.AttributeValueId = av.Id",
                        array("AttributeId" => "av.AttributeId", "Value" => "av.Value")
                    )
                    ->distinct(true)
                    ->where("av.Value is not null")
                    ->where('main_table.ProductId IN (?)', $sku)
                    ->where('main_table.Active IN (?)', '1')
                    ->where('av.Active IN (?)', '1')
                    ->order('main_table.Id');

                //echo $collectionB->getSelect();exit;
                //print_r($collectionB->getData());exit;

                if ($collectionB->getSize() && $collectionB->count()) {
                   
                    foreach ($collectionB as $item) {
                        $AttributeId =  $item->getData('AttributeId');
                        $log =  'MAGENTO PRODUCT ID('.$productId.') : =>>> START Update Binding Product Attribut '.$AttributeId.' with sku =>' . $sku . PHP_EOL;
                        
                        $Value =  $item->getData('Value');
                        $product = $this->productRepository->get($sku);
                        $attribute_id = 'attr_' . $AttributeId;
                        $attr = $product->getResource()->getAttribute($attribute_id);
                        if ($attr->usesSource()) {
                          
                            $option_id = $attr->getSource()->getOptionId($Value);
                        }

                        if ($AttributeId && $Value && $option_id) {

                            $product->setCustomAttribute($attribute_id, $option_id);
                             try {
                                $product->save($product);
                                $isProductedUpdated = true;
                                $this->logger->info('Attribute values imported for attribute id =>' . $attribute_id . 'Attribute Value id =>' . $option_id);
                            } catch (\Exception $e) {
                                $this->logger->info('Attribute values imported for attribute id =>' . $attribute_id . 'Attribute Value id =>' . $option_id . ' ' . $e->getMessage());
                                echo $e->getMessage() . PHP_EOL;
                            }
                        }
                        $log .= 'END Update Binding Product Attribute '.$AttributeId.' with sku =>' . $sku . PHP_EOL;
                        $this->getAttributeValueUpdateLogger($log);
                    }
                }

                if($isProductedUpdated){
                    $log = '#============================>>>>>>>> Updated Product Url : '.$itemProduct->getProductUrl() . PHP_EOL;
                }else{
                    $log = '#============================>>>>>>>> Not Updated Product Url : '.$itemProduct->getProductUrl() . PHP_EOL;
                }

                $this->getAttributeValueUpdateLogger($log);
                if ($i == 500) {
                    $i = 0;
                    $this->reindexByKey($indexLists);
                }
                $i++;
            }
        }
    }

    public function getAttributeValueUpdateLogger($log)
    {
        echo $log;
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
