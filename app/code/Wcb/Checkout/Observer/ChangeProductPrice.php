<?php

namespace Wcb\Checkout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Wurth\Shippingproduct\Helper\AddRemoveShippingProduct;

class ChangeProductPrice implements ObserverInterface
{
    /**
     * @var AddRemoveShippingProduct
     */
    protected $addRemoveShippingProduct;

    /**
     * ChangeProductPrice constructor.
     * @param AddRemoveShippingProduct $addRemoveShippingProduct
     */
    public function __construct(
        AddRemoveShippingProduct $addRemoveShippingProduct
    ) {
        $this->addRemoveShippingProduct = $addRemoveShippingProduct;
    }

    /**
     * @param Observer $observer
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $this->addRemoveShippingProduct->updateShippingProduct();
    }
}
