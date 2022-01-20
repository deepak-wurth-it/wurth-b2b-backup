<?php

namespace Amasty\Promo\Model\SalesRule;

use Amasty\Promo\Api\Data\GiftRuleInterface;
use Amasty\Promo\Api\Data\GiftRuleInterfaceFactory;
use Amasty\Promo\Model\ResourceModel\Rule;
use Amasty\Promo\Model\RuleResolver;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

class SaveHandler implements ExtensionInterface
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
     * Stores Free Gift Rule value from Sales Rule extension attributes
     *
     * @param \Magento\SalesRule\Model\Rule|\Magento\SalesRule\Model\Data\Rule $entity
     * @param array $arguments
     *
     * @return \Magento\SalesRule\Model\Rule|\Magento\SalesRule\Model\Data\Rule
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $attributes = $entity->getExtensionAttributes() ? : [];
        if (isset($attributes[GiftRuleInterface::EXTENSION_CODE])) {
            $ruleLinkId = $this->ruleResolver->getLinkId($entity);
            $amRule = $this->prepareData($attributes[GiftRuleInterface::EXTENSION_CODE], $ruleLinkId);
            $this->giftRuleResource->save($amRule);
        }

        if (in_array($entity->getSimpleAction(), \Amasty\Promo\Observer\Salesrule\Discount::PROMO_RULES)) {
            $entity->setData(\Magento\SalesRule\Model\Data\Rule::KEY_SIMPLE_FREE_SHIPPING, 0);
        }

        return $entity;
    }

    /**
     * @param array|GiftRuleInterface $inputData
     * @param int $ruleLinkId
     *
     * @return \Amasty\Promo\Model\Rule
     */
    private function prepareData($inputData, $ruleLinkId)
    {
        /** @var \Amasty\Promo\Model\Rule $amRule */
        $amRule = $this->giftRuleFactory->create();
        $this->giftRuleResource->load($amRule, $ruleLinkId, GiftRuleInterface::SALESRULE_ID);

        if ($inputData instanceof GiftRuleInterface) {
            $inputData->unsetData(GiftRuleInterface::ENTITY_ID)
                ->unsetData(GiftRuleInterface::SALESRULE_ID);
            $amRule->addData($inputData->getData());
        } else {
            unset($inputData[GiftRuleInterface::ENTITY_ID], $inputData[GiftRuleInterface::SALESRULE_ID]);
            $amRule->addData($inputData);
        }

        return $amRule->setSalesruleId($ruleLinkId);
    }
}
