<?php
namespace Wcb\QuantityImport\Controller\Product;

use Magento\Framework\App\Action\Context;
 
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
  
class AddToCart extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
    protected $_cart;
    protected $_productRepositoryInterface;
    protected $_url;
    protected $_responseFactory;
    protected $_logger;
 
 
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Psr\Log\LoggerInterface $logger
        )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_cart = $cart;
        $this->_productRepositoryInterface = $productRepositoryInterface;
        $this->_responseFactory = $responseFactory;
        $this->_url = $context->getUrl();
        $this->_logger = $logger;
        parent::__construct($context);
    }
 
    public function execute()
    {
        $productid = $this->getRequest()->getParam('product');
        $qty = $this->getRequest()->getParam('qty');
        $_product = $this->_productRepositoryInterface->getById($productid);
        $options = $_product->getOptions();
 
        $params = array (
            'product' => $_product->getId(),
            'qty' => $qty,
            'price' => $_product->getPrice()
        );
 
       $this->_cart->addProduct($_product, $params);
       $this->_cart->save();
    }
 
}