<?php

namespace Wcb\QuickOrder\Plugin\AdvancedCheckout;

use Magento\AdvancedCheckout\Helper\Data;
use Magento\AdvancedCheckout\Model\Cart;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\QuickOrder\Model\Config;
use Magento\Store\Model\StoreManagerInterface;
use Wcb\Checkout\Helper\Data as wcbCheckout;
use Wcb\Checkout\Helper\ManageProductStatus;

/**
 * Plugin class for AffectedItems modification in AdvancedCheckoutCart class.
 * @see \Magento\Checkout\Model\Cart\CartInterface
 */
class ModifyAffectedItemsPlugin
{
    /**
     * @var Data
     */
    private $checkoutHelper;

    /**
     * @var Config
     */
    private $quickOrderConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Data
     */
    private $advancedCheckoutHelper;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var ManageProductStatus
     */
    protected $manageProductStatus;
    /**
     * @var wcbCheckout
     */
    protected $wcbCheckout;

    /**
     * @param Data $checkoutHelper
     * @param Config $quickOrderConfig
     * @param StoreManagerInterface $storeManager
     * @param Data $advancedCheckoutHelper
     * @param ProductRepositoryInterface $productrepositoryInterface
     * @param ManageProductStatus $manageProductStatus
     * @param wcbCheckout $wcbCheckout
     */
    public function __construct(
        Data $checkoutHelper,
        Config $quickOrderConfig,
        StoreManagerInterface $storeManager,
        Data $advancedCheckoutHelper,
        ProductRepositoryInterface $productrepositoryInterface,
        ManageProductStatus $manageProductStatus,
        wcbCheckout $wcbCheckout
    ) {
        $this->checkoutHelper = $checkoutHelper;
        $this->quickOrderConfig = $quickOrderConfig;
        $this->storeManager = $storeManager;
        $this->advancedCheckoutHelper = $advancedCheckoutHelper;
        $this->manageProductStatus = $manageProductStatus;
        $this->productRepository = $productrepositoryInterface;
        $this->wcbCheckout = $wcbCheckout;
    }

    /**
     * Change item data to use it in the QuickOrder
     *
     * @param Cart $subject
     * @param \Closure $proceed
     * @param array $items
     * @param null|int $storeId
     * @return Cart
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws NoSuchEntityException
     */
    public function aroundSetAffectedItems(Cart $subject, \Closure $proceed, array $items, $storeId = null): Cart
    {
        if ($this->quickOrderConfig->isActive()) {
            foreach ($items as $sku => $item) {
                if (isset($item['item'], $item['code'])) {
                    $affectedData = $item['item'];
                    $code = $item['code'];
                    $item['sku'] = $sku;
                    $item['result'] = $this->getResultMessage($code);
                    $item['isError'] = (int)$this->isError($code);
                    $item['name'] = $this->getItemParam($affectedData, 'name', $code);
                    $item['url'] = $this->getItemParam($affectedData, 'url', $code);
                    $item['price'] = $this->getItemParam($affectedData, 'price', $code);
                    $item['thumbnailUrl'] = $this->getItemParam($affectedData, 'thumbnail_url', $code);
                    $item['qty'] = $this->getItemParam($affectedData, 'qty');
                    // for check discontinue product status
                    $productId =  $this->getItemParam($affectedData, 'id', $code);
                    $qty = $this->getItemParam($affectedData, 'qty');

                    $statusResult = $this->checkProductCustomStatus($productId, $qty);
                    if ($statusResult != '') {
                        if (!$statusResult['allow_add_to_cart']) {
                            $item['code'] = 'failed_qty_allowed';
                            $item['isError'] = 1;
                            $item['result'] = $statusResult['notAllowMsg'] . $statusResult['replacementMsg'];
                        }
                    }

                    $items[$sku] = $item;
                }
            }

            $storeId = $storeId === null ? $this->storeManager->getStore()->getId() : (int)$storeId;
            $affectedItems = $this->advancedCheckoutHelper->getSession()->getAffectedItems();
            if (!is_array($affectedItems)) {
                $affectedItems = [];
            }

            $affectedItems[$storeId] = $items;
            $this->advancedCheckoutHelper->getSession()->setAffectedItems($affectedItems);

            return $subject;
        }

        return $proceed($items, $storeId);
    }
    public function checkProductCustomStatus($productId, $qty)
    {
        try {
            $product = $this->productRepository->getById($productId);
            $minimumAndMasureQty = $this->wcbCheckout->getMinimumAndMeasureQty($product);
            $unitQty = $this->getNextMinimumQty($minimumAndMasureQty, $qty);
            return $this->manageProductStatus->checkDiscontinuedProductStatus($product, $unitQty, true);
        } catch (\Exception $e) {
            //echo $e->getMessage();
            return '';
        }
    }

    /**
     * Returns affected items.
     * Return format:
     * [
     *  'sku' => string
     *  'result' => string (see \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_*)
     *  'is_error' => int
     *  'name' => string
     *  'url' => string
     *  'price' => string
     *  'thumbnail_url' => string
     *  'qty' => string*
     * ]
     *
     * @param Cart $subject
     * @param \Closure $proceed
     * @param null|int $storeId [optional]
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @see \Magento\AdvancedCheckout\Model\Cart::prepareAddProductsBySku()
     */
    public function aroundGetAffectedItems(Cart $subject, \Closure $proceed, $storeId = null): array
    {
        if ($this->quickOrderConfig->isActive()) {
            $storeId = $storeId === null ? $this->storeManager->getStore()->getId() : (int)$storeId;
            $affectedItems = $this->advancedCheckoutHelper->getSession()->getAffectedItems();

            return isset($affectedItems[$storeId]) && is_array($affectedItems[$storeId])
                ? $affectedItems[$storeId] : [];
        }

        return $proceed($storeId);
    }

    /**
     * Item param getter.
     *
     * @param array $item
     * @param string $code
     * @param string|null $itemCode
     * @return string
     */
    private function getItemParam(array $item, $code, $itemCode = null): string
    {
        if ($itemCode !== null
            && ($itemCode == Data::ADD_ITEM_STATUS_FAILED_SKU
                || $itemCode == Data::ADD_ITEM_STATUS_FAILED_DISABLED)
        ) {
            return '';
        }

        return isset($item[$code])
            ? $item[$code]
            : '';
    }

    /**
     * Is code type error.
     *
     * @param string $code
     * @return bool
     */
    private function isError($code): bool
    {
        $allowedCodes = [
            Data::ADD_ITEM_STATUS_SUCCESS,
            Data::ADD_ITEM_STATUS_FAILED_CONFIGURE
        ];

        return !in_array($code, $allowedCodes);
    }

    /**
     * Get result message.
     *
     * @param string $code
     * @return string
     */
    private function getResultMessage($code): string
    {
        $message = $this->checkoutHelper->getMessage($code);
        if (($message === '') && ($code === Data::ADD_ITEM_STATUS_FAILED_QTY_INCREMENTS)) {
            $message = __('You should correct the quantity for the product.');
        }
        if ($code === Data::ADD_ITEM_STATUS_FAILED_OUT_OF_STOCK) {
            $message = __('The SKU is out of stock.');
        }
        if ($code === Data::ADD_ITEM_STATUS_FAILED_QTY_ALLOWED) {
            $message = __('We don\'t have the quantity you requested.');
        }

        return (string) $message;
    }

    public function getNextMinimumQty($minimumAndMasureQty, $userQty)
    {
        if ($userQty && $minimumAndMasureQty) {
            $minimumQty = (int) ($userQty / $minimumAndMasureQty);
            return ($minimumQty > 0) ? $minimumQty : 1;
        }
        return 1;
    }
}
