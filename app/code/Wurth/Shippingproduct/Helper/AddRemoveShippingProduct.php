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
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;
use Wcb\Checkout\Helper\Data as CheckoutHelper;
use Wurth\Shippingproduct\Helper\Data as ShippingProductHelper;

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

    protected $cartRepositoryInterface;

    protected $shippingProductHelper;

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
        CheckoutHelper $checkoutHelper,
        CartRepositoryInterface $cartRepositoryInterface,
        ShippingProductHelper $shippingProductHelper
    ) {
        $this->formKey = $formKey;
        $this->cart = $cart;
        $this->helperData = $helperData;
        $this->productRepository = $productRepository;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        $this->registry = $registry;
        $this->checkoutHelper = $checkoutHelper;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->shippingProductHelper = $shippingProductHelper;
        parent::__construct($context);
    }

    /**
     * Update shipping product
     *
     * @param string $quote
     * @param string $api
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function updateShippingProduct($quote = '', $api = '')
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

        // set Price using API
        $this->setPriceUsingApi($quote);

        foreach ($items as $item) {
            if ($item->getSku() === $this->helperData->getShippingProductCode()) {
                $shippingProductExist = true;
            } else {
                $subtotal += $item->getRowTotal();
            }
        }

        if ($subtotal < $cartAmountLimit && !$shippingProductExist && $subtotal !== 0) {
            if (!$quote->getData('pickup_store_id')) {
                $this->addShippingProduct($quote, $api);
            }

            // set Price using API
            $this->setPriceUsingApi($quote);
        }

        if (($subtotal >= $cartAmountLimit && $shippingProductExist) || $subtotal === 0) {
            $this->removeShippingProduct($items, $quote, $api);
        }
    }

    public function setPriceUsingApi($quote)
    {
        $items = $quote->getAllVisibleItems();
        foreach ($items as $item) {
            $item = ($item->getParentItem() ? $item->getParentItem() : $item);
            $priceData = $this->checkoutHelper->getPriceApiData($item->getProduct()->getProductCode());
            $price = isset($priceData['price']) ? $priceData['price'] : '';
            if (isset($priceData['discount']) && $priceData['discount'] != 0) {
                $price = $priceData['discount_price'];
            }
            $unitOfMeasureId = $item->getProduct()->getBaseUnitOfMeasureId();

            //$type = $this->checkoutHelper->getType($unitOfMeasureId);

            if ($unitOfMeasureId == '2' &&
                $item->getProduct()->getProductCode() != $this->shippingProductHelper->getConfig(ShippingProductHelper::SHIPPING_PRODUCT_CODE) &&
                $price
            ) {
                $price = ($price * 1) / 100;
            }
            /*var_dump($price);
            exit;*/
            $price = (float) $price;
            $item->setCustomPrice($price);
            $item->setOriginalCustomPrice($price);
            $item->getProduct()->setIsSuperMode(true);
            $item->calcRowTotal();

            $item->getQuote()->collectTotals();
            $this->cart->getQuote()->setTriggerRecollect(1);
            $this->cart->getQuote()->collectTotals()->save();
            // $this->cart->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();
        }
    }

    /**
     * Add shipping product
     *
     * @param $quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function addShippingProduct($quote, $api = '')
    {
        try {
            $product = $this->productRepository->get($this->helperData->getShippingProductCode());
            if ($product) {
                $params = [
                    'form_key' => $this->formKey->getFormKey(),
                    'product' => $product->getId(),
                    'qty' => 1
                ];
                $request = new \Magento\Framework\DataObject();
                $request->setData($params);
                if ($api=='') {
                    $this->cart->addProduct($product, $params);
                    $this->cart->save();
                    $this->cart->getQuote()->setTriggerRecollect(1);
                    $this->cart->getQuote()->collectTotals()->save();
                    // update total
                    $quoteObject = $this->cartRepositoryInterface->get($this->cart->getQuote()->getId());
                } else {
                    // update total
                    $quoteObject = $this->cartRepositoryInterface->get($quote->getId());
                    $quoteObject->addProduct($product, $request);
                }

                $quoteObject->setTriggerRecollect(1);
                $quoteObject->setIsActive(true);
                $quoteObject->collectTotals()->save();
            }
        } catch (Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * Remove shipping product
     *
     * @param $items
     * @param string $quote
     * @param string $api
     * @throws Exception
     */
    public function removeShippingProduct($items, $quote = '', $api = '')
    {
        try {
            foreach ($items as $item) {
                if ($item->getSku() === $this->helperData->getShippingProductCode()) {
                    if ($api=='') {
                        $this->cart->removeItem($item->getId());
                        $this->cart->save();
                        $this->cart->getQuote()->setTriggerRecollect(1);
                        $this->cart->getQuote()->collectTotals()->save();

                        // update total
                        $quoteObject = $this->cartRepositoryInterface->get($this->cart->getQuote()->getId());
                    } else {
                        $quoteObject = $this->cartRepositoryInterface->get($quote->getId());
                        $quoteObject->deleteItem($item);
                    }
                    $quoteObject->setTriggerRecollect(1);
                    $quoteObject->setIsActive(true);
                    $quoteObject->collectTotals()->save();

                    break;
                }
            }
        } catch (Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }
}
