<?php

namespace Amasty\Conditions\Model\Rule\Condition;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Product extends \Magento\Rule\Model\Condition\AbstractCondition
{
    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    private $stockItem;

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\CatalogInventory\Api\StockStateInterface $stockItem,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->stockItem = $stockItem;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'weight'    => __('Weight'),
            'quantity_in_stock' => __('Quantity In Stock'),
        ];
        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        return 'numeric';
    }

    /**
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);

        return $element;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $quote = $model;
        if ($quote instanceof \Magento\Quote\Model\Quote\Address) {
            $quote = $model->getQuote();
        }
        $isValid = false;

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($quote->getAllItems() as $item) {
            $attrValue = $this->getAttribute() === 'quantity_in_stock'
                ? $this->getStockQty($item)
                : $item->getWeight();

            $quote->setData($this->getAttribute(), $attrValue);
            $isValid = parent::validate($quote);

            if ($isValid) {
                break;
            }
        }

        return $isValid;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return float
     */
    private function getStockQty(\Magento\Quote\Model\Quote\Item $item)
    {
        $product = $item->getProduct();
        if ($product->getTypeId() === Configurable::TYPE_CODE) {
            $options = $product->getCustomOptions();
            $simple = $options['simple_product'];
            $stockQty = $this->stockItem->getStockQty(
                $simple->getProduct()->getId(),
                $item->getStore()->getWebsiteId()
            );
        } else {
            $stockQty = $this->stockItem->getStockQty(
                $product->getId(),
                $item->getStore()->getWebsiteId()
            );
        }

        return $stockQty;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOperatorSelectOptions()
    {
        $operators = [
            '>=' => __('equals or greater than'),
            '<=' => __('equals or less than'),
            '>' => __('greater than'),
            '<' => __('less than'),
        ];
        $type = $this->getInputType();
        $result = [];
        $operatorByType = $this->getOperatorByInputType();

        foreach ($operators as $operatorKey => $operatorValue) {
            if (!$operatorByType || in_array($operatorKey, $operatorByType[$type])) {
                $result[] = ['value' => $operatorKey, 'label' => $operatorValue];
            }
        }

        return $result;
    }
}
