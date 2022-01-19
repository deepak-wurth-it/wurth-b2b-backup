<?php

namespace Amasty\Promo\Model;

use Amasty\Promo\Api\Data\GiftRuleInterface;
use Amasty\Promo\Api\Data\GiftRuleInterfaceFactory;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\SalesRule\Api\Data\RuleExtensionFactory;

class RuleResolver
{
    /**
     * @var RuleExtensionFactory
     */
    private $extensionFactory;

    /**
     * @var MetadataPool
     */
    private $metadata;

    /**
     * @var ResourceModel\Rule
     */
    private $giftRuleResource;

    /**
     * @var GiftRuleInterfaceFactory
     */
    private $giftRuleFactory;

    public function __construct(
        RuleExtensionFactory $extensionFactory,
        MetadataPool $metadata,
        GiftRuleInterfaceFactory $giftRuleFactory,
        ResourceModel\Rule $giftRuleResource
    ) {
        $this->extensionFactory = $extensionFactory;
        $this->metadata = $metadata;
        $this->giftRuleResource = $giftRuleResource;
        $this->giftRuleFactory = $giftRuleFactory;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $salesRule
     *
     * @return \Amasty\Promo\Model\Rule
     */
    public function getFreeGiftRule($salesRule)
    {
        if (!$salesRule->hasData(GiftRuleInterface::RULE_NAME)) {
            $extensionAttributes = $salesRule->getExtensionAttributes();
            if (!$extensionAttributes) {
                $extensionAttributes = $this->extensionFactory->create();
            }
            if (!$extensionAttributes->getAmpromoRule()) {
                /** @var GiftRuleInterface $amRule */
                $amRule = $this->giftRuleFactory->create();
                $this->giftRuleResource->load($amRule, $this->getLinkId($salesRule), GiftRuleInterface::SALESRULE_ID);
                $extensionAttributes->setAmpromoRule($amRule);
            }
            $salesRule->setExtensionAttributes($extensionAttributes);

            $salesRule->setData(GiftRuleInterface::RULE_NAME, $extensionAttributes->getAmpromoRule());
        }

        return $salesRule->getDataByKey(GiftRuleInterface::RULE_NAME);
    }

    /**
     * @param \Magento\Rule\Model\AbstractModel $rule
     * @return int|null
     */
    public function getLinkId(\Magento\Rule\Model\AbstractModel $rule)
    {
        return $rule->getDataByKey($this->getLinkField());
    }

    /**
     * @return string
     */
    public function getLinkField()
    {
        return $this->metadata->getMetadata(\Magento\SalesRule\Api\Data\RuleInterface::class)->getLinkField();
    }
}
