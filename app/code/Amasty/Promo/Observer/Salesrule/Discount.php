<?php

namespace Amasty\Promo\Observer\Salesrule;

use Amasty\Promo\Model\Config;
use Amasty\Promo\Model\Config\Source\GiftRepresentationMode;
use Amasty\Promo\Model\DiscountCalculator;
use Amasty\Promo\Model\Rule;
use Amasty\Promo\Model\RuleResolver;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;
use Psr\Log\LoggerInterface;

/**
 * Event name salesrule_validator_process
 */
class Discount implements ObserverInterface
{
    const PROMO_RULES = [
        Rule::PER_PRODUCT,
        Rule::SAME_PRODUCT,
        Rule::SPENT,
        Rule::WHOLE_CART,
        Rule::EACHN,
    ];

    /**
     * @var \Amasty\Promo\Helper\Item
     */
    private $promoItemHelper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var DiscountCalculator
     */
    private $discountCalculator;

    /**
     * @var RuleResolver
     */
    private $ruleResolver;

    /**
     * @var State
     */
    private $state;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        \Amasty\Promo\Helper\Item $promoItemHelper,
        ProductRepositoryInterface $productRepository,
        DiscountCalculator $discountCalculator,
        RuleResolver $ruleResolver,
        State $state,
        LoggerInterface $logger,
        Config $config
    ) {
        $this->promoItemHelper = $promoItemHelper;
        $this->productRepository = $productRepository;
        $this->discountCalculator = $discountCalculator;
        $this->ruleResolver = $ruleResolver;
        $this->state = $state;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * @param Observer $observer
     *
     * @return Data|void
     */
    public function execute(Observer $observer)
    {
        try {
            /** @var Item $item */
            $item = $observer->getItem();
            /** @var \Magento\SalesRule\Model\Rule $rule */
            $rule = $observer->getRule();

            if ($this->checkItemForPromo($rule, $item)) {
                /** @var Data $result */
                $result = $observer->getResult();
                if (!$item->getAmDiscountAmount()) {
                    $baseDiscount = $this->discountCalculator->getBaseDiscountAmount($observer->getRule(), $item);
                    $discount = $this->discountCalculator->getDiscountAmount($observer->getRule(), $item);

                    $result->setBaseAmount($baseDiscount);
                    $result->setAmount($discount);
                    $item->setAmBaseDiscountAmount($baseDiscount);
                    $item->setAmDiscountAmount($discount);
                } elseif ($this->state->getAreaCode() === Area::AREA_WEBAPI_REST) {
                    $result->setAmount($item->getAmDiscountAmount());
                    $result->setBaseAmount($item->getAmBaseDiscountAmount());
                }
            }
        } catch (LocalizedException $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * Is discount should be given for current cart item
     *
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param Item $item
     *
     * @return bool
     * @throws LocalizedException
     */
    public function checkItemForPromo($rule, $item)
    {
        if (!in_array($rule->getSimpleAction(), self::PROMO_RULES)
            || !$this->promoItemHelper->isPromoItem($item)
            || (int)$rule->getId() !== (int)$item->getAmpromoRuleId()
            || ($item->getParentItem() && $item->getParentItem()->getProductType() == Type::TYPE_BUNDLE)
        ) {
            return false;
        }
        $ampromoRule = $this->ruleResolver->getFreeGiftRule($rule);

        if (($this->isFullDiscountRule($ampromoRule)
                && $item->getProductType() !== 'giftcard'
                && ($this->config->getGiftRepresentationMode() === GiftRepresentationMode::SHOW_ZERO_PRICE))
            || ($this->isFullDiscountRule($ampromoRule)
                && $item->getProductType() === 'giftcard'
                && $this->config->getGiftRepresentationMode() === GiftRepresentationMode::SHOW_ZERO_PRICE)
        ) {
            return false;
        }

        if ($item->getParentItem() && $item->getParentItem()->getProductType() == 'bundle') {
            $itemSku = $item->getParentItem()->getProduct()->getData('sku');
        } else {
            $itemSku = $item->getProduct()->getData('sku');
        }

        if ($rule->getSimpleAction() === Rule::SAME_PRODUCT) {
            return true;
        }

        $promoSkuArray = explode(",", $ampromoRule->getSku());
        foreach ($promoSkuArray as &$promoSku) {
            if (trim($promoSku) == $itemSku) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Rule $ampromoRule
     *
     * @return bool
     */
    private function isFullDiscountRule($ampromoRule)
    {
        return $this->discountCalculator->isFullDiscount(
            [
                'minimal_price' => $ampromoRule->getMinimalItemsPrice(),
                'discount_item' => $ampromoRule->getItemsDiscount()
            ]
        );
    }
}
