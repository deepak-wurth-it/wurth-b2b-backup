<?php

namespace Wurth\Shippingproduct\Plugin\Checkout\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Wurth\Shippingproduct\Helper\AddRemoveShippingProduct;

class Cart
{
    /**
     * @var AddRemoveShippingProduct
     */
    protected $addRemoveShippingProduct;

    /**
     * Cart constructor.
     * @param AddRemoveShippingProduct $addRemoveShippingProduct
     */
    public function __construct(
        AddRemoveShippingProduct $addRemoveShippingProduct
    ) {
        $this->addRemoveShippingProduct = $addRemoveShippingProduct;
    }

    /**
     * After cart save plugin
     *
     * @param \Magento\Checkout\Model\Cart $subject
     * @param $result
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterSave(\Magento\Checkout\Model\Cart $subject, $result)
    {
        $this->addRemoveShippingProduct->updateShippingProduct($subject->getQuote());
    }
}
