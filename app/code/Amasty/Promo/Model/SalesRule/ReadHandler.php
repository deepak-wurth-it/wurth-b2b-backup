<?php

namespace Amasty\Promo\Model\SalesRule;

use Amasty\Promo\Api\Data\GiftRuleInterface;
use Amasty\Promo\Api\Data\GiftRuleInterfaceFactory;
use Amasty\Promo\Model\ResourceModel\Rule;
use Amasty\Promo\Model\RuleResolver;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

class ReadHandler implements ExtensionInterface
{
    /**
     * @var Rule
     */
    private $giftRuleResource;

    /**
     * @var GiftRuleInterfaceFactory
     */
    private $giftRuleFactory;

    /**
     * @var RuleResolver
     */
    private $ruleResolver;

    public function __construct(
        GiftRuleInterfaceFactory $amRuleFactory,
        Rule $giftRuleResource,
        RuleResolver $ruleResolver
    ) {
        $this->giftRuleResource = $giftRuleResource;
        $this->giftRuleFactory = $amRuleFactory;
        $this->ruleResolver = $ruleResolver;
    }

    /**
     * Fill Sales Rule extension attributes with related Free Gift Rule
     *
     * @param \Magento\SalesRule\Model\Rule|\Magento\SalesRule\Model\Data\Rule $entity
     * @param array $arguments
     * @return \Magento\SalesRule\Model\Rule|\Magento\SalesRule\Model\Data\Rule
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $ruleLinkId = $this->ruleResolver->getLinkId($entity);

        if ($ruleLinkId) {
            /** @var array $attributes */
            $attributes = $entity->getExtensionAttributes() ?: [];
            /** @var \Amasty\Promo\Model\Rule $amRule */
            $amRule = $this->giftRuleFactory->create();
            $this->giftRuleResource->load($amRule, $ruleLinkId, GiftRuleInterface::SALESRULE_ID);
            $attributes[GiftRuleInterface::EXTENSION_CODE] = $amRule;
            $entity->setData(GiftRuleInterface::RULE_NAME, $amRule);
            $entity->setExtensionAttributes($attributes);
        }

        return $entity;
    }
}
