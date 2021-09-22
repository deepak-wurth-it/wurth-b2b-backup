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
class ProductProcessor
{
    protected $product;
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface           $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface      $productRepository,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Pim\Product\Model\ProductFactory                    $pimProductFactory,
        \Magento\Catalog\Model\ProductFactory                $productFactory,
        \Pim\Category\Model\PimProductsCategoriesFactory     $pimProductsCategoriesFactory,
        LoggerInterface $logger


    )
    {

        $this->storeManager = $storeManager;
        $this->pimProductFactory = $pimProductFactory;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
        $this->pimProductsCategoriesFactory = $pimProductsCategoriesFactory;
        $this->logger = $logger;
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
            ->addFieldToFilter('magento_sync_status', [['eq' => '0'],['null' => true]]);

        $x = 0;
        if ($collection->getSize() && $collection->count()) {

            foreach ($collection as $item) {

                $this->product = $this->productFactory->create();

                try {

                    $name = $this->setPimProductName($item);
                    $pimProductId = $this->getPimProductId($item);
                    $magentoCategoryId = $this->setMagentoCategoryIds($item);
                    $isProductExist = $this->product->load($this->product->getIdBySku($pimProductId));

                    if ($isProductExist && is_object($isProductExist)) {
                        $this->product = $isProductExist;
                        echo 'Product Updated =>>'.$isProductExist->getId().PHP_EOL;
                    }else{
                        echo 'Product Created =>>'.PHP_EOL;
                    }
                    echo 'Start Product Id '.$pimProductId.PHP_EOL;
                    if ($pimProductId && $name) {
                        $this->product->setName($name);
                        $this->setProductSku($item);
                        $this->setPimProductWeight($item);
                        $this->setPimProductLongDescription($item);
                        $this->setProductWebsiteIds(); // Default Website ID
                        $this->setProductStoreId();
                        $this->setProductPrice();
                        $this->setProductVisibility();
                        $this->setPimProductShortDescription($item);
                        $this->setProductCreatedAt();
                        $this->setProductTaxClassId();
                        $this->setProductAttributeSetId();
                        $this->setProductType();
                        $this->setPimProductSource();
                        $this->setProductRequired();
                        $this->setProductImages();
                        $this->setProductImages();
                        $this->setProductCustomUrl();
                        $this->setManufacturerCountryName();
                        $this->setCategoryId($magentoCategoryId);
                        $this->setPimProductSource();
                        $this->setProductStatus($item);
                        $this->setPimStockData($item);
                        $this->setQuantityAndStockStatus($item);
                        //print_r(get_class_methods($this->product));exit;
                        try {
                            $this->product->save();
                            echo 'End Product Id '.$pimProductId.PHP_EOL;


                        } catch (\Exception $e) {
                            echo $e->getMessage().PHP_EOL;
                            $this->logger->info('Error importing product sku: ' . $pimProductId . '. ' . $e->getMessage());
                            continue;
                        }
                        echo $this->product->getId();exit;
                        if ($this->product->getId()) {
                            $this->updatePimProductRow($item);
                            echo 'Created Product For Pim Code  ' . $pimProductId . PHP_EOL;
                        }

                    }
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
                $x++;
                if ($x == 10) {
                    break;
                }
            }

        }
    }

    public function setPimProductName($item)
    {
        $name = '';
        if ($item) {
            $name = $item->getData('Name') ? $item->getData('Name') : '';
        }
        return $name;
    }

    public function setProductSku($item)
    {
        if ($this->product && $item) {
            $pimProductId = $item->getData('Id') ? $item->getData('Id') : '';
            $this->product->setSku($pimProductId);
        }

    }

    public function setPimSaleQuantity($item)
    {
        $saleQuantity = '';
        if ($item) {
            $saleQuantity = $item->getData('MinimumSalesUnitQuantity') ? $item->getData('MinimumSalesUnitQuantity') : 1;
        }
        return $saleQuantity;
    }

    public function setPimProductWeight($item)
    {
        if ($this->product && $item) {
            $weight = $item->getData('NetWeight') ? $item->getData('NetWeight') : 0.00;
            $this->product->setWeight($weight);
        }


    }

    public function setPimProductLongDescription($item)
    {
        $desc = $item->getData('LongDescription') ? $item->getData('LongDescription') : '';
        if ($desc &&  $this->product) {
            $this->product->setDescription($item);
        }

    }

    public function setPimProductShortDescription($item)
    {

        $shortDesc = $item->getData('ShortDescription') ? $item->getData('ShortDescription') : '';

        if ($shortDesc &&  $this->product) {
            $this->product->setShortDescription($shortDesc);
        }

    }

    public function setMagentoCategoryIds($item)
    {

        $pimProductsCategoriesCollection = $this->pimProductsCategoriesFactory->create();
        $pimProductsCategoriesCollection = $pimProductsCategoriesCollection->getCollection()
            ->addFieldToFilter('ProductId', ['eq' => $this->getPimProductId($item)]);
        $magentoCategoryId = '';
        if ($pimProductsCategoriesCollection && is_object($pimProductsCategoriesCollection)) {
            $magentoCategoryId = $pimProductsCategoriesCollection->getColumnValues('magento_category_id');
            $magentoCategoryId = implode(', ', $magentoCategoryId);
        }
        return $magentoCategoryId;

    }

    public function setProductImages()
    {
        if ($this->product) {
            //$this->product->setImage('/sample/test.jpg');
            //$this->product->setSmallImage('/sample/test.jpg');
            // $this->product->setThumbnail('/sample/test.jpg');
        }

    }

    public function setProductCustomUrl()
    {
        if ($this->product) {
            //$this->product->setUrlKey('abc');
        }
    }

    public function setManufacturer()
    {
        if ($this->product) {
            //$this->product->setManufacturer(28) //manufacturer id
        }

    }

    public function setManufacturerCountryName()
    {
        if ($this->product) {

            //$this->product->setCountryOfManufacture('AF') //country of manufacture (2-letter country code)

        }

    }

    public function setPimStockData($item)
    {
        if ($this->product) {
            $saleQuantity = (round($this->setPimSaleQuantity($item)) > 1) ?? '1';
            $this->product->setStockData(
                [
                    'use_config_manage_stock' => 0,
                    'manage_stock' => 1,
                    'min_sale_qty' => $saleQuantity,
                    //'max_sale_qty' => 2,
                    'is_in_stock' => true,
                    'qty' => 100
                ]
            );
        }


    }

    public function setQuantityAndStockStatus($item)
    {
        if ($this->product) {
            $this->product->setQuantityAndStockStatus(
                [
                    'is_in_stock' => 1,
                    'qty' => 100
                ]
            );
        }


    }

    public function setCategoryId($magentoCategoryId)
    {
        if ($magentoCategoryId && $this->product) {
            $this->product->setCategoryIds(array($magentoCategoryId));
        }

    }

    public function setPimProductSource()
    {
        if ($this->product) {
            $this->product->setCustomAttribute('product_source', 'magento_admin');
        }



    }

    public function setProductStatus($item)
    {
        if ($item->getData('Active') && $item->getData('Active') == 1) {

            $this->product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);

        } else {
            $this->product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);

        }


    }

    public function setProductType()
    {
        if ($this->product) {
            $this->product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        }

    }

    public function setProductRequired()
    {
        if ($this->product) {
            $this->product->setUpdateRequired('1');
        }

    }

    public function setProductAttributeSetId()
    {
        if ($this->product) {
            return $this->product->setAttributeSetId(4);
        }
    }

    public function setProductTaxClassId()
    {
        if ($this->product) {
            $this->product->setTaxClassId(0);
        }

    }

    public function setProductCreatedAt()
    {
        if ($this->product) {
            $this->product->setCreatedAt(strtotime('now'));
        }


    }

    public function setProductVisibility()
    {
        if ($this->product) {
            $this->product->setVisibility(4);

        }
    }

    public function setProductPrice()
    {
        if ($this->product) {
            $this->product->setPrice('1.00');
        }

    }

    public function setProductStoreId()
    {
        if ($this->product) {
            $this->product->setStoreId(0); // Default store ID
        }
    }

    public function setProductWebsiteIds()
    {
        if ($this->product) {
            $this->product->setWebsiteIds(array(1)); // Default Website ID
        }
    }

    public function getPimProductId($item)
    {
        $pimProductId = '';
        if ($item) {

            $pimProductId = $item->getData('Id') ? $item->getData('Id') : '';

        }
        return $pimProductId;

    }

    public function updatePimProductRow($item){
        if($this->product && $item){
            $item->setData('magento_sync_status','1');
            $item->setData('magento_product_id',$this->product->getId());
            $item->save();
        }
    }
}

