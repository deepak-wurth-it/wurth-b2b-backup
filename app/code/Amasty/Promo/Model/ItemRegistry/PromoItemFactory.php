<?php

namespace Amasty\Promo\Model\ItemRegistry;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for @see PromoItemData
 */
class PromoItemFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $sku
     * @param int $qty
     * @param int|null $ruleId
     * @param int|int $ruleType
     * @param float|null $minimalPrice
     * @param string|null $discountItem
     * @param float|null $discountAmount
     * @param bool|null $autoAdd
     *
     * @return PromoItemData
     */
    public function create(
        $sku,
        $qty,
        $ruleId,
        $ruleType = \Amasty\Promo\Model\Rule::RULE_TYPE_ALL,
        $minimalPrice = null,
        $discountItem = null,
        $discountAmount = null,
        $autoAdd = null
    ) {
        return $this->objectManager->create(
            PromoItemData::class,
            [
                'sku' => $sku,
                'qty' => $qty,
                'ruleId' => $ruleId,
                'ruleType' => $ruleType,
                'minimalPrice' => $minimalPrice,
                'discountItem' => $discountItem,
                'discountAmount' => $discountAmount,
                'autoAdd' => $autoAdd
            ]
        );
    }
}
