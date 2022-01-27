<?php

namespace Amasty\Conditions\Plugin\SalesRule\Condition;

/**
 * This plugin can be disabled by Amasty_Rules, because have same functionality
 */
class ProductPlugin
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * @param \Magento\Rule\Model\Condition\Product\AbstractProduct $subject
     * @return \Magento\Rule\Model\Condition\Product\AbstractProduct
     */
    public function afterLoadAttributeOptions(
        \Magento\Rule\Model\Condition\Product\AbstractProduct $subject
    ) {
        $subject->setAttributeOption(
            array_merge(
                $subject->getAttributeOption(),
                [
                    'quote_item_sku' => __('Custom Options SKU'),
                    'quote_item_row_total_incl_tax' => __('Row total in cart with tax')
                ]
            )
        );

        return $subject;
    }

    /**
     * @param \Magento\Rule\Model\Condition\Product\AbstractProduct $subject
     * @param \Magento\Framework\Model\AbstractModel $model
     */
    public function beforeValidate(
        \Magento\Rule\Model\Condition\Product\AbstractProduct $subject,
        \Magento\Framework\Model\AbstractModel $model
    ) {
        $product = $model->getProduct();
        if (!$product instanceof \Magento\Catalog\Model\Product) {
            try {
                $product = $this->productRepository->getById($model->getProductId());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                return;
            }
            $model->setProduct($product);
        }

        if ($product && $product->getTypeId() !== 'skip') {
            $product->setQuoteItemSku($model->getSku());
            $product->setQuoteItemRowTotalInclTax($model->getBaseRowTotalInclTax());
        }
    }
}
