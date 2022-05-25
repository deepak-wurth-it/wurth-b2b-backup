<?php

namespace Wurth\Shippingproduct\Helper;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;
use Wcb\Checkout\Helper\Data as CheckoutHelper;

class AddRemoveShippingProduct extends AbstractHelper
{
    /**
     * @var Data
     */
    protected $helperData;
    /**
     * @var FormKey
     */
    protected $formKey;
    /**
     * @var Cart
     */
    protected $cart;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var Session
     */
    protected $checkoutSession;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var Registry
     */
    protected $registry;
    /**
     * @var CheckoutHelper
     */
    protected $checkoutHelper;

    /**
     * AddRemoveShippingProduct constructor.
     * @param Context $context
     * @param Data $helperData
     * @param FormKey $formKey
     * @param Cart $cart
     * @param ProductRepositoryInterface $productRepository
     * @param Session $checkoutSession
     * @param LoggerInterface $logger
     * @param Registry $registry
     * @param CheckoutHelper $checkoutHelper
     */
    public function __construct(
        Context $context,
        Data $helperData,
        FormKey $formKey,
        Cart $cart,
        ProductRepositoryInterface $productRepository,
        Session $checkoutSession,
        LoggerInterface $logger,
        Registry $registry,
        CheckoutHelper $checkoutHelper
    ) {
        $this->formKey = $formKey;
        $this->cart = $cart;
        $this->helperData = $helperData;
        $this->productRepository = $productRepository;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        $this->registry = $registry;
        $this->checkoutHelper = $checkoutHelper;
        parent::__construct($context);
    }

    /**
     * Update shipping product
     *
     * @param string $quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function updateShippingProduct($quote = '')
    {
        $skipPlugin = $this->registry->registry('skip_plugin');
        if ($skipPlugin === 'true') {
            return;
        }

        if ($quote === '') {
            $quote = $this->checkoutSession->getQuote();
        }
        $items = $quote->getAllVisibleItems();
        $cartAmountLimit = $this->helperData->getCartAmountLimit();

        $shippingProductExist = false;
        $subtotal = 0;

        foreach ($items as $item) {
            if ($item->getSku() === $this->helperData->getShippingProductCode()) {
                $shippingProductExist = true;
            } else {
                $subtotal += $item->getRowTotal();
            }
            // set Price using API
            $this->setPriceUsingApi($item);
        }

        if ($subtotal < $cartAmountLimit && !$shippingProductExist && $subtotal !== 0) {
            $this->addShippingProduct($quote);
        }

        if (($subtotal >= $cartAmountLimit && $shippingProductExist) || $subtotal === 0) {
            $this->removeShippingProduct($items);
        }
    }
    public function setPriceUsingApi($item)
    {
        $item = ($item->getParentItem() ? $item->getParentItem() : $item);
        $priceData = $this->checkoutHelper->getPriceApiData($item->getProduct()->getProductCode());
        $price = isset($priceData['price']) ? $priceData['price'] : '';
        if (isset($priceData['discount']) && $priceData['discount'] != 0) {
            $price = $priceData['discount_price'];
        }

        $item->setCustomPrice($price);
        $item->setOriginalCustomPrice($price);
        $item->getProduct()->setIsSuperMode(true);
    }

    /**
     * Add shipping product
     *
     * @param $quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function addShippingProduct($quote)
    {
        try {
            $product = $this->productRepository->get($this->helperData->getShippingProductCode());
            if ($product) {
                $params = [
                    'form_key' => $this->formKey->getFormKey(),
                    'product' => $product->getId(),
                    'qty' => 1
                ];
                $this->cart->addProduct($product, $params);
                $this->cart->save();
                $this->cart->getQuote()->setTriggerRecollect(1);
                $this->cart->getQuote()->collectTotals()->save();
            }
        } catch (Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * Remove shipping product
     *
     * @param $items
     * @throws Exception
     */
    public function removeShippingProduct($items)
    {
        try {
            foreach ($items as $item) {
                if ($item->getSku() === $this->helperData->getShippingProductCode()) {
                    $this->cart->removeItem($item->getId());
                    $this->cart->save();
                    $this->cart->getQuote()->setTriggerRecollect(1);
                    $this->cart->getQuote()->collectTotals()->save();
                    break;
                }
            }
        } catch (Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }
}
