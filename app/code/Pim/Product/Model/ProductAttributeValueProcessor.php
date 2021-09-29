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
        LoggerInterface $logger


    )
    {

        $this->storeManager = $storeManager;
        $this->pimProductFactory = $pimProductFactory;
        $this->productAttributeValuesFactory = $productAttributeValuesFactory;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
        $this->logger = $logger;
    }

    /**
     * @param array $fixtures
     * @throws \Exception
     */
    public function install()
    {

        $mageProduct = $this->productFactory->create();
        $collection = $mageProduct->getCollection()
            ->addAttributeToFilter('status', ['eq' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED])
            ->addAttributeToFilter('update_required', ['eq' => '1']);
        if ($collection->getSize() && $collection->count()) {
            foreach ($collection as $itemProduct) {
               $sku = $itemProduct->getSku();
               if(!$sku){
                   continue;
               }
               $collectionB = $this->productAttributeValuesFactory->create();
               $collectionB = $collectionB->getCollection();
               $collectionB->getSelect()
                   ->join(
                        array("av" => "attributevalues"),
                        "main_table.AttributeValueId = av.Id",
                        array("AttributeId" => "av.AttributeId","Value" => "av.Value")
                    )
                    ->distinct(true)
                    ->where("av.Value is not null")
                    ->where('main_table.ProductId IN (?)', $sku)
                   ->where('main_table.Active IN (?)', '1')
                   ->where('av.Active IN (?)', '1')
                    ->order('main_table.Id');
               //print_r($collectionB->getData());exit;
                if ($collectionB->getSize() && $collectionB->count()) {
                    foreach ($collectionB as $item) {
                        echo 'START Update Binding Product Attribute with sku =>' .$sku.PHP_EOL;
                       $AttributeId =  $item->getData('AttributeId');
                       $Value =  $item->getData('Value');
                       $product = $this->productRepository->get($sku);
                       $attribute_id = 'attr_'.$AttributeId;
                       $attr = $product->getResource()->getAttribute($attribute_id);
                        if ($attr->usesSource()) {
                            $option_id = $attr->getSource()->getOptionId($Value);
                        }

                        if($AttributeId && $Value && $option_id) {

                            $product->setCustomAttribute($attribute_id, $option_id);
                            try {
                                $product->save($product);
                                $this->logger->info('Attribute values imported for attribute id =>' . $attribute_id . 'Attribute Value id =>'.$option_id);

                            } catch (\Exception $e) {
                                $this->logger->info('Attribute values imported for attribute id =>' . $attribute_id . 'Attribute Value id =>'.$option_id.' '. $e->getMessage());
                                echo $e->getMessage().PHP_EOL;
                            }


                        }
                        echo 'END Update Binding Product Attribute with sku =>' .$sku.PHP_EOL;
                    }
                }
                //die($attribute_id.'----'.$Value);
            }

        }
    }
}
