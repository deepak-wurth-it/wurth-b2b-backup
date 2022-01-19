<?php

namespace Amasty\Promo\Observer\Admin;

/**
 * Remove unsupported rule conditions
 */
class DeleteConditionHandle implements \Magento\Framework\Event\ObserverInterface
{
    const NOT_SUPPORTED_CONDITIONS = [
        'Amasty\Conditions\Model\Rule\Condition\Address|billing_address_country',
        'Amasty\Conditions\Model\Rule\Condition\Address|payment_method',
        'Amasty\Conditions\Model\Rule\Condition\Address|shipping_address_line'
    ];

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    public function __construct(\Magento\Framework\App\Request\Http $request)
    {
        $this->request = $request;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return DeleteConditionHandle
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $moduleName = $this->request->getModuleName();
        if ($moduleName === 'sales_rule') {
            $conditions = $observer->getAdditional()->getConditions();
            $promoConditions = [];

            foreach ($conditions as $condition) {
                if ($this->isAdvancedConditions($condition)) {
                    foreach ($condition['value'] as $key => $condAttribute) {
                        if (in_array($condAttribute['value'], self::NOT_SUPPORTED_CONDITIONS)) {
                            unset($condition['value'][$key]);
                        }
                    }
                }
                $promoConditions[] = $condition;
            }

            $observer->getAdditional()->setConditions($promoConditions);
        }

        return $this;
    }

    /**
     * @param $condition
     *
     * @return bool
     */
    private function isAdvancedConditions($condition)
    {
        return is_array($condition)
            && isset($condition['label'])
            && $condition['label']->getText() === \Amasty\Conditions\Model\Constants::MODULE_NAME;
    }
}
