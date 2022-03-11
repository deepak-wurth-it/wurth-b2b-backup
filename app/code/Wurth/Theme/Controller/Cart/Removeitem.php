<?php

namespace Wurth\Theme\Controller\Cart;

use Exception;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;

class Removeitem extends Action
{
    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var ManagerInterface
     */
    protected $messageManager;
    /**
     * @var CustomerCart
     */
    protected $cart;

    protected $resultFactory;

    /**
     * Removeitem constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     * @param ManagerInterface $messageManager
     * @param CustomerCart $cart
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        ManagerInterface $messageManager,
        CustomerCart $cart
    ) {
        $this->logger = $logger;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->messageManager = $messageManager;
        $this->cart = $cart;
        $this->resultFactory = $context->getResultFactory();

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $response = $this->_resultJsonFactory->create();
        $result = [];
        try {
            $itemId = $this->getRequest()->getParam('item_id');
            $isRemove = $this->removeCartItemById($itemId);
            $layout = $this->resultFactory->create(ResultFactory::TYPE_PAGE)
                ->addHandle('checkout_cart_index')
                ->getLayout();

            $itemForm = "";

            if ($isRemove) {
                if ($layout->getBlock('checkout.cart.form')) {
                    $itemForm = $layout->getBlock('checkout.cart.form')->toHtml();
                }
                $quote = $this->cart->getQuote();
                $totalItems = count($quote->getAllItems());
                if ($totalItems == 0) {
                    if ($layout->getBlock('checkout.cart.empty')) {
                        $itemForm = $layout->getBlock('checkout.cart.empty')->toHtml();
                    }
                }
                $result['success'] = "true";
                $result['item_form'] = $itemForm;
                $result['message'] = __("Your item has been deleted.");
                $this->messageManager->addSuccess($result['message']);
            } else {
                $result['success'] = "false";
                $result['item_form'] = "";
                $result['message'] = __("Something went wrong please try again.");
                $this->messageManager->addError($result['message']);
            }
        } catch (Exception $e) {
            $result['success'] = "false";
            $result['item_form'] = "";
            $result['message'] = __("Something went wrong please try again.");
            $this->messageManager->addError($result['message']);
        }
        $response->setData($result);
        return $response;
    }

    /**
     * @param $itemId
     * @return bool
     */
    public function removeCartItemById($itemId)
    {
        try {
            $this->cart->removeItem($itemId);
            $this->cart->save();
            $this->cart->getQuote()->setTriggerRecollect(1);
            $this->cart->getQuote()->collectTotals()->save();
            return true;
        } catch (Exception $e) {
            $this->logger->info($e->getMessage());
            return false;
        }
    }
}
