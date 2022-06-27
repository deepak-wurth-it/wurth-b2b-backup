<?php

namespace Wurth\Shippingproduct\Observer;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Wcb\Checkout\Helper\Data;

class AddToCartProductAfter implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $data;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    private $_storeManager;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        Data $data,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->_storeManager = $storeManager;
        $this->data = $data;
        $this->productRepository = $productRepository;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            $item = $observer->getEvent()->getData('quote_item');
            $productId = $item->getProduct()->getId();
            if ($item->getQty() != $item->getWcbOrderUnit()) {
                $product = clone $this->productRepository->getById($productId, false, $this->_storeManager->getStore()->getId());
                $quantityUnitByQuantity = $this->data->getQuantityUnitByQuantity($item->getQty(), $product);
                $wcbQuantityByUnit = $this->data->getMinimumAndMeasureQty($product);
                $item->setWcbQuantityOrdered($wcbQuantityByUnit);
                $item->setWcbOrderUnit($quantityUnitByQuantity);
            }
        } catch (Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }
}
