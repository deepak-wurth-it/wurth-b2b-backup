<?php

namespace Amasty\Promo\Plugin\Quote;

use Amasty\Promo\Helper\Item as HelperItem;
use Amasty\Promo\Model\Config;
use Amasty\Promo\Model\Config\Source\GiftRepresentationMode;
use Amasty\Promo\Model\DiscountCalculator;
use Amasty\Promo\Model\Prefix;
use Amasty\Promo\Model\Storage;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\Data\Rule;

/**
 * Cort Item compatibility with Promo Cart Item
 */
class Item
{
    /**
     * @var HelperItem
     */
    private $promoItemHelper;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var DiscountCalculator
     */
    private $discountCalculator;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var State
     */
    private $state;

    /**
     * @var Prefix
     */
    private $prefix;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        HelperItem $promoItemHelper,
        ScopeConfigInterface $scopeConfig,
        DiscountCalculator $discountCalculator,
        RuleRepositoryInterface $ruleRepository,
        State $state,
        Prefix $prefix,
        Config $config
    ) {
        $this->promoItemHelper = $promoItemHelper;
        $this->scopeConfig = $scopeConfig;
        $this->discountCalculator = $discountCalculator;
        $this->ruleRepository = $ruleRepository;
        $this->state = $state;
        $this->prefix = $prefix;
        $this->config = $config;
    }

    /**
     * @param AbstractItem $subject
     * @param $key
     * @param null $value
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSetData(AbstractItem $subject, $key, $value = null)
    {
        if (!is_string($key)) {
            return [$key, $value];
        }

        $fields = [
            'price',
            'base_price',
            'custom_price',
            'original_custom_price',
            'price_incl_tax',
            'base_price_incl_tax',
            'row_total',
            'row_total_incl_tax',
            'base_row_total',
            'base_row_total_incl_tax',
            'weee_tax_applied_amount',
            'base_weee_tax_applied_amount',
            'weee_tax_applied_row_amount',
            'base_weee_tax_applied_row_amnt',
            'weee_tax_applied_amount_incl_tax',
            'base_weee_tax_applied_amount_incl_tax',
            'weee_tax_applied_row_amount_incl_tax',
            'base_weee_tax_applied_row_amnt_incl_tax',
        ];

        if (in_array($key, $fields)) {
            if (($this->promoItemHelper->isPromoItem($subject)
                    && $this->isFullDiscount($subject)
                    && $subject->getNotUsePricePlugin() !== true
                    && $subject->getProduct()->getTypeId() !== 'giftcard'
                    && ($this->config->getGiftRepresentationMode() === GiftRepresentationMode::SHOW_ZERO_PRICE))
                || ($this->isFullDiscount($subject)
                    && $subject->getNotUsePricePlugin() !== true
                    && $subject->getProduct()->getTypeId() === 'giftcard'
                    && $this->config->getGiftRepresentationMode() === GiftRepresentationMode::SHOW_ZERO_PRICE)
            ) {
                if (isset(Storage::$cachedQuoteItemPricesWithTax[$subject->getSku()][$key])) {
                    return [$key, Storage::$cachedQuoteItemPricesWithTax[$subject->getSku()][$key]];
                }

                return [$key, 0];
            }
        }

        return [$key, $value];
    }

    /**
     * @param AbstractItem $item
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isFullDiscount(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        $buyRequest = $item->getBuyRequest();
        $discount = isset($buyRequest['options']['discount']) ? $buyRequest['options']['discount'] : false;

        return $this->discountCalculator->isFullDiscount($discount);
    }

    /**
     * @param AbstractItem $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return bool
     */
    public function aroundRepresentProduct(
        AbstractItem $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product $product
    ) {
        if ($result = $proceed($product)) {
            $productRuleId = (int)$product->getData('ampromo_rule_id');
            $itemRuleId = (int)$this->promoItemHelper->getRuleId($subject);

            if ($productRuleId || $itemRuleId) {
                return $productRuleId === $itemRuleId;
            }
        }

        return $result;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $subject
     * @param string"array $result
     *
     * @return array|mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetMessage(AbstractItem $subject, $result)
    {
        if ($this->promoItemHelper->isPromoItem($subject)) {
            if ($this->state->getAreaCode() === Area::AREA_ADMINHTML) {
                $this->prefix->addPrefixToName($subject);
            }

            $customMessage = $this->getCustomMessage($subject);
            if ($customMessage) {
                if (is_string($result)) {
                    $result .= __("\n" . $customMessage);
                } else {
                    $result[] = __($customMessage);
                }
            }
        }

        return $result;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     *
     * @return string|null
     */
    protected function getCustomMessage($item)
    {
        $customMessage = $this->scopeConfig->getValue(
            'ampromo/messages/cart_message',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (!$customMessage) {
            /** @var \Magento\SalesRule\Api\Data\RuleSearchResultInterface $rules */
            $rule = $this->ruleRepository->getById($item->getAmpromoRuleId());
            $ruleLabel = $this->getStoreLabel($rule, $item->getStoreId());
            if ($ruleLabel) {
                $customMessage = $ruleLabel->getStoreLabel();
            }
        }

        return $customMessage;
    }

    /**
     * Get Rule label by specified store
     *
     * @param \Magento\SalesRule\Model\Data\Rule $rule
     * @param int|null $storeId
     *
     * @return \Magento\SalesRule\Model\Data\RuleLabel|bool
     */
    private function getStoreLabel(Rule $rule, $storeId = null)
    {
        $labels = (array)$rule->getStoreLabels();

        if (isset($labels[$storeId])) {
            return $labels[$storeId];
        } elseif (isset($labels[0]) && $labels[0]) {
            return $labels[0];
        }

        return false;
    }
}
