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
use Magento\Framework\View\LayoutFactory;
use Magento\Quote\Model\Quote\ItemFactory;
use Psr\Log\LoggerInterface;
use Wcb\Checkout\Helper\Data as WcbCheckoutHelper;

class Updateitem extends Action
{
    protected $layoutFactory;

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
    /**
     * @var ResultFactory
     */
    protected $resultFactory;
    /**
     * @var WcbCheckoutHelper
     */
    protected $wcbCheckoutHelper;
    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * Updateitem constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     * @param ManagerInterface $messageManager
     * @param CustomerCart $cart
     * @param LayoutFactory $layoutFactory
     * @param WcbCheckoutHelper $wcbCheckoutHelper
     * @param ItemFactory $itemFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        ManagerInterface $messageManager,
        CustomerCart $cart,
        LayoutFactory $layoutFactory,
        WcbCheckoutHelper $wcbCheckoutHelper,
        ItemFactory $itemFactory
    ) {
        $this->resultFactory = $context->getResultFactory();
        $this->logger = $logger;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->messageManager = $messageManager;
        $this->cart = $cart;
        $this->layoutFactory = $layoutFactory;
        $this->wcbCheckoutHelper = $wcbCheckoutHelper;
        $this->itemFactory = $itemFactory;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $layout = $this->resultFactory->create(ResultFactory::TYPE_PAGE)
            ->addHandle('checkout_cart_index')
            ->getLayout();

        $itemForm = "";

        $response = $this->_resultJsonFactory->create();
        $result = [];
        try {
            $itemId = $this->getRequest()->getParam('item_id');
            $qty = $this->getRequest()->getParam('qty');
            $qty = $this->getTotalQty($itemId, $qty);

            $isItemUpdate = $this->updateCartItemQty($itemId, $qty);
            if ($isItemUpdate) {
                if ($layout->getBlock('checkout.cart.form')) {
                    $itemForm = $layout->getBlock('checkout.cart.form')->toHtml();
                }
                $result['success'] = "true";
                $result['item_form'] = $itemForm;
                $result['message'] = __("Item has been updated successfully.");
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
     * @param $qty
     * @return float|int
     */
    public function getTotalQty($itemId, $qty)
    {
        $item = $this->itemFactory->create()->load($itemId);
        if ($item->getId()) {
            $product = $this->wcbCheckoutHelper->getLoadProduct($item->getProductId());
            if ($product) {
                $qty = $this->wcbCheckoutHelper->getTotalQty($product, $qty);
            }
        }

        return $qty;
    }

    /**
     * @param $itemId
     * @param $qty
     * @return bool
     */
    public function updateCartItemQty($itemId, $qty)
    {
        try {
            $itemData = [$itemId => ['qty' => $qty]];
            $this->cart->updateItems($itemData)->save();
            $this->cart->getQuote()->setTriggerRecollect(1);
            $this->cart->getQuote()->collectTotals()->save();
            return true;
        } catch (Exception $e) {
            $this->logger->info($e->getMessage());
            return false;
        }
    }
}
