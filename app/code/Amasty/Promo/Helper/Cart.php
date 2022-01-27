<?php

namespace Amasty\Promo\Helper;

use Amasty\Promo\Model\ItemRegistry\PromoItemData;
use Amasty\Promo\Model\Product as ProductStock;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;

/**
 * Add promo items to cart, update total quantity of cart
 */
class Cart
{
    /**
     * @var Messages
     */
    private $promoMessagesHelper;

    /**
     * @var ProductStock
     */
    private $product;

    public function __construct(
        Messages $promoMessagesHelper,
        ProductStock $product
    ) {
        $this->promoMessagesHelper = $promoMessagesHelper;
        $this->product = $product;
    }

    /**
     * @param Product $product
     * @param int $qty
     * @param PromoItemData $promoItemData
     * @param array $requestParams
     * @param Quote|null $quote
     *
     * @return bool
     */
    public function addProduct(
        Product $product,
        $qty,
        $promoItemData,
        array $requestParams,
        Quote $quote
    ) {
        if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
            $qty = $this->resolveQty($product, $qty, $quote);
        }
        if ($qty == 0) {
            return false;
        }

        $ruleId = $promoItemData->getRuleId();
        //TODO ST-1949 process not free items with custom_price
        $requestParams['qty'] = $qty;
        $requestParams['options']['ampromo_rule_id'] = $ruleId;
        $requestParams['options']['discount'] = $promoItemData->getDiscountArray();

        if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            if (!isset($requestParams['bundle_option'])) {
                $requestParams = array_merge($requestParams, $this->getBundleOptions($product));
            }
        }

        try {
            $item = $quote->addProduct($product, new \Magento\Framework\DataObject($requestParams));

            if ($item instanceof \Magento\Quote\Model\Quote\Item) {
                $item->setData('ampromo_rule_id', $ruleId);
            } else {
                throw new LocalizedException(__($item));
            }

            //qty for promoItemData will be reserved later
            $promoItemData->isDeleted(false);
            if (!$quote->hasData('is_copy')) {
                $this->promoMessagesHelper->showMessage(
                    __(
                        "Free gift <strong>%1</strong> was added to your shopping cart",
                        $product->getName()
                    ),
                    false,
                    true,
                    true
                );
            }

            return true;
        } catch (\Exception $e) {
            $this->promoMessagesHelper->showMessage(
                $e->getMessage(),
                true,
                true
            );
        }

        return false;
    }

    /**
     * Get all the default selection products used in bundle product
     * @param Product $product
     * @return array
     */
    private function getBundleOptions(Product $product)
    {
        $selectionCollection = $product->getTypeInstance()
            ->getSelectionsCollection(
                $product->getTypeInstance()->getOptionsIds($product),
                $product
            );
        $bundleOptions = [];
        foreach ($selectionCollection as $selection) {
            if (!$selection->getIsDefault()) {
                continue;
            }

            $bundleOptions['bundle_option'][$selection->getOptionId()][] = $selection->getSelectionId();
            $bundleOptions['bundle_option_qty'][$selection->getOptionId()] = $selection->getSelectionQty();
        }

        return $bundleOptions;
    }

    /**
     * @param Product $product
     * @param int $qty
     * @param Quote $quote
     *
     * @return float|int
     */
    private function resolveQty($product, $qty, $quote)
    {
        $availableQty = $this->product->checkAvailableQty($product->getSku(), $qty, $quote);

        if ($availableQty <= 0) {
            $this->promoMessagesHelper->addAvailabilityError($product);

            $availableQty = 0;
        } elseif ($availableQty < $qty) {
            $this->promoMessagesHelper->showMessage(
                __(
                    "We apologize, but requested quantity of free gift <strong>%1</strong>"
                    . " is not available at the moment",
                    $product->getName()
                ),
                false,
                true
            );
        }

        return $availableQty;
    }

    /**
     * @param Quote $quote
     */
    public function updateTotalQty($quote)
    {
        $quote->setItemsCount(0);
        $quote->setItemsQty(0);
        $quote->setVirtualItemsQty(0);

        $items = $quote->getAllVisibleItems();
        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            $children = $item->getChildren();
            if ($children && $item->isShipSeparately()) {
                foreach ($children as $child) {
                    if ($child->getProduct()->getIsVirtual()) {
                        $qty = $quote->getVirtualItemsQty() + $child->getQty() * $item->getQty();
                        $quote->setVirtualItemsQty($qty);
                    }
                }
            }

            if ($item->getProduct()->getIsVirtual()) {
                $quote->setVirtualItemsQty($quote->getVirtualItemsQty() + $item->getQty());
            }
            $quote->setItemsCount($quote->getItemsCount() + 1);
            $quote->setItemsQty((float)$quote->getItemsQty() + $item->getQty());
        }
    }
}
