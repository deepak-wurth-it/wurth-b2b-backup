<?php

/**
 * Copyright © 2018 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wcb\ApiConnect\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartItemExtensionFactory;
use Wcb\Checkout\Helper\Data;
class QuotePlugin
{

    /**
     * @var CartItemExtensionFactory
     */
    protected $cartItemExtension;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var Data
     */
    private $helperData;

    /**
     * @param CartItemExtensionFactory $cartItemExtension
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        CartItemExtensionFactory $cartItemExtension,
        ProductRepositoryInterfaceFactory $productRepository,
        Data $helperData
    ) {
        $this->cartItemExtension = $cartItemExtension;
        $this->productRepository = $productRepository;
        $this->helperData = $helperData;
    }

    /**
     * Add attribute values
     *
     * @param CartRepositoryInterface $subject ,
     * @param   $quote
     * @return  $quoteData
     */
    public function afterGet(
        CartRepositoryInterface $subject,
        $quote
    ) {
        $quoteData = $this->setAttributeValue($quote);
        return $quoteData;
    }

    /**
     * set value of attributes
     *
     * @param   $product ,
     * @return  $extensionAttributes
     */
    private function setAttributeValue($quote)
    {
       // $data = [];
        if ($quote->getItemsCount()) {
            foreach ($quote->getItems() as $item) {
                $data = [];
                $extensionAttributes = $item->getExtensionAttributes();
                if ($extensionAttributes === null) {
                    $extensionAttributes = $this->cartItemExtension->create();
                }

                $productData = $this->productRepository->create()->get($item->getSku());
                $getBaseUnitOfMeasureId = $productData->getBaseUnitOfMeasureId();
                $ourCustomData = $this->helperData->getType($productData->getBaseUnitOfMeasureId());

                $extensionAttributes->setImage($productData->getThumbnail());
                $extensionAttributes->setProductCode($productData->getProductCode());
                $extensionAttributes->setSalesUnitOfMeasureValue($ourCustomData);
                $extensionAttributes->setSalesMinimumQty($productData->getMinimumSalesUnitQuantity());
                $extensionAttributes->setRowSubTotal($item->getRowTotal());
                $item->setExtensionAttributes($extensionAttributes);
            }
        }

        return $quote;
    }

    /**
     * Add attribute values
     *
     * @param CartRepositoryInterface $subject ,
     * @param   $quote
     * @return  $quoteData
     */
    public function afterGetActiveForCustomer(
        CartRepositoryInterface $subject,
        $quote
    ) {
        $quoteData = $this->setAttributeValue($quote);
        return $quoteData;
    }
}
