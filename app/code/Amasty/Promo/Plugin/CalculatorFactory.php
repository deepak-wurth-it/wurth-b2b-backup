<?php

namespace Amasty\Promo\Plugin;

class CalculatorFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected $classByType = [
        \Amasty\Promo\Model\Rule::SAME_PRODUCT => \Amasty\Promo\Model\Rule\Action\Discount\SameProduct::class,
        \Amasty\Promo\Model\Rule::PER_PRODUCT => \Amasty\Promo\Model\Rule\Action\Discount\Product::class,
        \Amasty\Promo\Model\Rule::WHOLE_CART => \Amasty\Promo\Model\Rule\Action\Discount\Cart::class,
        \Amasty\Promo\Model\Rule::SPENT => \Amasty\Promo\Model\Rule\Action\Discount\Spent::class,
        \Amasty\Promo\Model\Rule::EACHN => \Amasty\Promo\Model\Rule\Action\Discount\Eachn::class,
    ];

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    public function aroundCreate(
        \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory $subject,
        \Closure $proceed,
        $type
    ) {
        if (isset($this->classByType[$type])) {
            return $this->objectManager->create($this->classByType[$type]);
        }

        return $proceed($type);
    }
}
