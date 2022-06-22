<?php

namespace Wcb\QuantityImport\Controller\Product;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;

class AddToCart extends Action
{
    protected $_resultPageFactory;
    protected $_cart;
    protected $_productRepositoryInterface;
    protected $_url;
    protected $_responseFactory;
    protected $_logger;
    protected $resultJsonFactory;
    protected $messageManager;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Cart $cart,
        ProductRepositoryInterface $productRepositoryInterface,
        ResponseFactory $responseFactory,
        LoggerInterface $logger,
        JsonFactory $resultJsonFactory,
        ManagerInterface $messageManager
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_cart = $cart;
        $this->_productRepositoryInterface = $productRepositoryInterface;
        $this->_responseFactory = $responseFactory;
        $this->_url = $context->getUrl();
        $this->_logger = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = [];
        $resultJson = $this->resultJsonFactory->create();

        try {
            $productId = $this->getRequest()->getParam('product');
            $qty = $this->getRequest()->getParam('qty');
            if (!$productId) {
                $result['success'] = false;
                $result['message'] = __("The product that was requested doesn't exist.");
                return $resultJson->setData($result);
            }
            $_product = $this->_productRepositoryInterface->getById($productId);

            $params = [
                'product' => $_product->getId(),
                'qty' => $qty
            ];

            $this->_cart->addProduct($_product, $params);
            $this->_cart->save();
            $message = __(sprintf("You added %s to your shopping cart.", $_product->getName()));
            $result['success'] = true;
            $result['message'] = $message;
            $this->messageManager->addSuccess($message);
        } catch (Exception $e) {
            $result['success'] = false;
            $result['message'] = __($e->getMessage());
            $this->messageManager->addError(__($e->getMessage()));
        }

        return $resultJson->setData($result);
    }
}
