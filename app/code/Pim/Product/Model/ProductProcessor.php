<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Pim\Product\Model;



/**
 * Setup sample attributes
 *
 * Class Attribute
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductProcessor
{

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        //\Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Pim\Product\Model\ProductFactory $pimProductFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory

    ) {

        $this->storeManager = $storeManager;
        $this->pimProductFactory = $pimProductFactory;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * @param array $fixtures
     * @throws \Exception
     */
    public function install()
    {

        $objPimProduct = $this->pimProductFactory->create();
        $collection = $objPimProduct->getCollection()
            ->addFieldToFilter('Status', ['eq' => '1']);
      
        if ($collection->getSize() && $collection->count()) {

            $i = 0;
            foreach ($collection as $item) {
                $product = $this->productFactory->create();

                try {
                    $minSaleQty = $item->getData('MinimumSalesUnitQuantity') ?  $item->getData('MinimumSalesUnitQuantity') : 1;
                    $weight = $item->getData('NetWeight') ? $item->getData('NetWeight') : 0.00;
                    $code = $item->getData('Code') ? $item->getData('Code') : '';
                    $name = $item->getData('Name') ? $item->getData('Name') : '';
                    $description = $item->getData('LongDescription') ? $item->getData('LongDescription') : '';
                    $shortDescription = $item->getData('ShortDescription') ? $item->getData('ShortDescription') : '';
                    
                    if ($code && $name && empty($product->getIdBySku($code)) ) {
                        echo 'test'. PHP_EOL;
                        $product->setName($name);
                        $product->setSku($code);
                        $product->setWeight($weight);
                        $product->setDescription($description);
                        $product->setShortDescription($shortDescription);
                        $product->setWebsiteIds([1]);
                        $product->setVisibility(4);
                        $product->setPrice([1]);
                        $product->setAttributeSetId(4);
                        $product->setCreatedAt(strtotime('now'));
                        $product->setTaxClassId(0);
                        //$product->setManufacturer(28) //manufacturer id
                        //$product->setCountryOfManufacture('AF') //country of manufacture (2-letter country code)
                        $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
                        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                        //$product->setCategoryIds(array(39258, 23358)); //assign product to categories
                        //$product->setImage('/sample/test.jpg');
                        //$product->setSmallImage('/sample/test.jpg');
                        // $product->setThumbnail('/sample/test.jpg');
                        $product->setStockData(
                            [
                                'use_config_manage_stock' => 0,
                                'manage_stock' => 1,
                                'min_sale_qty' => $minSaleQty,
                                //'max_sale_qty' => 2,
                                'is_in_stock' => 1,
                                'qty' => 100
                            ]
                        );
                        $product->save();
                        
                        if($product->getId()){
                            echo 'Created Product For Pim Code  ' . $code . PHP_EOL;
                        }
                        //$product->setCustomAttribute('ts_dimensions_length',$productDetails["length"]);
                        // $product->setCustomAttribute('ts_dimensions_width',$productDetails["width"]);
                        // $product->setCustomAttribute('ts_dimensions_height',$productDetails["height"]);
                        $i++;
                        if ($i == 10) {
                            break;
                        }

                    }
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }
        }
    }
}
