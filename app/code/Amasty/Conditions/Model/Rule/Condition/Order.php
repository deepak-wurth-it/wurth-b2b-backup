<?php

namespace Amasty\Conditions\Model\Rule\Condition;

class Order extends \Magento\SalesRule\Model\Rule\Condition\Product\Combine
{

    /**
     * @var \Amasty\Conditions\Model\Order\ResourceModel\Collection
     */
    private $reportsFactory;

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Amasty\Conditions\Model\Order\ResourceModel\CollectionFactory $reportsFactory,
        \Magento\SalesRule\Model\Rule\Condition\Product $ruleConditionProduct,
        array $data = []
    ) {
        parent::__construct($context, $ruleConditionProduct, $data);
        $this->setType(\Amasty\Conditions\Model\Rule\Condition\Order::class)->setValue(null);
        $this->reportsFactory = $reportsFactory->create();
    }

    /**
     * Load array
     *
     * @param array $arr
     * @param string $key
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadArray($arr, $key = 'conditions')
    {
        $this->setAttribute($arr['attribute']);
        $this->setOperator($arr['operator']);
        parent::loadArray($arr, $key);

        return $this;
    }

    /**
     * Load attribute options
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'average_order_value' => __('Average Order Value'),
            'sales_amount' => __('Total Sales Amount'),
            'of_placed_orders' => __('Number of Placed Orders'),
            'order_num' => __('Number of Completed Orders'),
        ];
        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Load operator options
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadOperatorOptions()
    {
        $operators = [
            '>=' => __('equals or greater than'),
            '<=' => __('equals or less than'),
            '>' => __('greater than'),
            '<' => __('less than'),
        ];
        $this->setOperatorOption($operators);

        return $this;
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }

    /**
     * Return as html
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() .
            __(
                "If %1 %2 %3 for a subselection of items in cart matching %4 of these conditions:",
                $this->getAttributeElement()->getHtml(),
                $this->getOperatorElement()->getHtml(),
                $this->getValueElement()->getHtml(),
                $this->getAggregatorElement()->getHtml()
            );

        if ($this->getId() !== '1') {
            $html .= $this->getRemoveLinkHtml();
        }

        return $html;
    }

    /**
     * Validate
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $quote = $model;
        $num = 0;
        if (!$quote instanceof \Magento\Quote\Model\Quote) {
            $quote = $model->getQuote();
        }

        if ($quote->getCustomerId()) {
            $orders = $this->reportsFactory->calculateTotalsOrder($quote->getCustomerId(), $this->getAttribute());

            switch ($this->getAttribute()) {
                case 'order_num':
                    $num = $orders->getSize();
                    break;

                case 'of_placed_orders':
                    $num = $orders->getSize();
                    break;

                case 'sales_amount':
                    $num = $orders->getLastItem()->getLifetime();
                    break;

                case 'average_order_value':
                    $num = $orders->getLastItem()->getAverage();
                    break;
            }
        }

        return $this->validateAttribute($num);
    }
}
