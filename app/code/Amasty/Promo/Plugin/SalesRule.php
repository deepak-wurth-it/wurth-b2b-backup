<?php

namespace Amasty\Promo\Plugin;

use Amasty\Base\Model\Serializer;
use Amasty\Promo\Api\Data\GiftRuleInterface;
use Amasty\Promo\Api\Data\GiftRuleInterfaceFactory;
use Amasty\Promo\Model\Rule;

class SalesRule
{
    /**
     * @var GiftRuleInterfaceFactory
     */
    private $giftRuleFactory;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        GiftRuleInterfaceFactory $giftRuleFactory,
        Serializer $serializer
    ) {
        $this->giftRuleFactory = $giftRuleFactory;
        $this->serializer = $serializer;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $subject
     * @param \Magento\SalesRule\Model\Rule $salesRule
     *
     * @return \Magento\SalesRule\Model\Rule
     */
    public function afterLoadPost(\Magento\SalesRule\Model\Rule $subject, $salesRule)
    {
        /** @var array $attributes */
        $attributes = $salesRule->getExtensionAttributes() ?: [];
        if (!isset($attributes[GiftRuleInterface::EXTENSION_CODE])
            || !is_array($attributes[GiftRuleInterface::EXTENSION_CODE])
        ) {
            return $salesRule;
        }

        /** @var \Amasty\Promo\Model\Rule $amRule */
        $amRule = $this->giftRuleFactory->create();
        $amRule->setData($attributes[GiftRuleInterface::EXTENSION_CODE]);

        $attributes[GiftRuleInterface::EXTENSION_CODE] = $amRule;
        $subject->setExtensionAttributes($attributes);

        // Unset actions if rule applicable for whole cart
        if ($subject->getSimpleAction() === Rule::WHOLE_CART) {
            $subject->setActions($subject->getActionsInstance());
        }

        return $salesRule;
    }
}
