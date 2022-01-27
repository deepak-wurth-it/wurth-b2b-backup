<?php

namespace Amasty\Conditions\Observer\Admin;

class AddNewConditionHandle implements \Magento\Framework\Event\ObserverInterface
{
    const CONDITIONS = [
        'Advanced Conditions' => ['Product', 'Address'],
        'Customer Attributes' => ['Customer Attributes']
    ];

    const CONDITION_MODEL_PATH = 'Amasty\Conditions\Model\Rule\Condition\\';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return AddNewConditionHandle
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $additional = $observer->getAdditional();
        $conditions = $additional->getConditions();
        if (!is_array($conditions)) {
            $conditions = [];
        }

        foreach (self::CONDITIONS as $groupName => $customConditions) {
            $attributes = [];
            foreach ($customConditions as $condition) {
                $typeCode = str_replace(" ", "", $condition);
                $conditionObject = $this->objectManager->get(self::CONDITION_MODEL_PATH . $typeCode);
                $conditionAttributes = $conditionObject->loadAttributeOptions()->getAttributeOption();
                foreach ($conditionAttributes as $code => $label) {
                    $attributes[] = [
                        'value' => self::CONDITION_MODEL_PATH . $typeCode . '|' . $code,
                        'label' => $label,
                    ];
                }
            }

            if ($groupName === 'Advanced Conditions') {
                $attributes[] = [
                    'value' => \Amasty\Conditions\Model\Rule\Condition\Order::class,
                    'label' => __('Orders Subselection')
                ];
            }
            $conditions[] = [
                'value' => $attributes,
                'label' => __($groupName),
            ];
        }

        $additional->setConditions($conditions);

        return $this;
    }
}
