<?php

namespace Wcb\Checkout\Model\Plugin;

class Cart
{
    protected $productRepository;
    protected $storeManager;
    protected $checkoutSession;
    protected $logger;


    public function __construct( \Magento\Catalog\Api\ProductRepositoryInterface $productrepositoryInterface, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\Session\SessionManagerInterface $checkoutSession, \Psr\Log\LoggerInterface $loggerInterface )
    {
        $this->productRepository = $productrepositoryInterface;
        $this->storeManager      = $storeManager;
        $this->checkoutSession   = $checkoutSession;
        $this->logger            = $loggerInterface;
    }
    public function beforeAddProduct( \Magento\Checkout\Model\Cart $subject, $productInfo, $requestInfo = null )
    {  
        $productId =  $productInfo->getData('entity_id');
        $product = $this->productRepository->getById($productId);     
        $minimum_sales_quantity = $product->getMinimumSalesUnitQuantity();
        $minimum_sales_quantity = $product->getMinimumSalesUnitQuantity();
        if($minimum_sales_quantity && $requestInfo['qty']){
            $requestInfo['qty'] = $requestInfo['qty'] * $minimum_sales_quantity;
        }
        
          
        return [$productInfo,$requestInfo];
    }
}
