<?php
declare(strict_types=1);

namespace Amasty\Promo\ViewModel\Product\View\Type;

use Magento\Bundle\Block\Catalog\Product\View\Type\Bundle as BundleBlock;
use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Helper\Output;
use Magento\Catalog\Helper\Product as CatalogProduct;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Bundle implements ArgumentInterface
{
    /**
     * @var Output
     */
    private $outputHelper;

    /**
     * @var CatalogProduct
     */
    private $catalogProduct;

    /**
     * @var array
     */
    private $optionsByProduct;

    public function __construct(
        Output $outputHelper,
        CatalogProduct $catalogProduct
    ) {
        $this->outputHelper = $outputHelper;
        $this->catalogProduct = $catalogProduct;
    }

    /**
     * @param Product $product
     * @param string $attributeHtml
     * @param string $attributeName
     * @return string
     */
    public function getProductAttributeHtml(Product $product, string $attributeHtml, string $attributeName): string
    {
        return $this->outputHelper->productAttribute($product, $attributeHtml, $attributeName);
    }

    /**
     * @param Option $option
     * @param Product $product
     * @param BundleBlock $block
     * @return string
     */
    public function getOptionHtml(Option $option, Product $product, BundleBlock $block): string
    {
        $optionBlock = $block->getChildBlock($option->getType());

        if ($optionBlock) {
            return $optionBlock
                ->setProduct($product)
                ->setOption($option)
                ->toHtml();
        }

        return '';
    }

    /**
     * Retrieve bundle product options for provided product
     *
     * @param Product $product
     * @param bool $stripSelection
     * @return array
     */
    public function getOptions(Product $product, bool $stripSelection = false): array
    {
        $productId = $product->getId();

        // ignore $stripSelection param for caching, same logic in native bundle block
        if (!isset($this->optionsByProduct[$productId])) {
            /** @var Type $typeInstance */
            $typeInstance = $product->getTypeInstance();
            $typeInstance->setStoreFilter($product->getStoreId(), $product);
            $optionCollection = $typeInstance->getOptionsCollection($product);
            $selectionCollection = $typeInstance->getSelectionsCollection(
                $typeInstance->getOptionsIds($product),
                $product
            );

            $this->optionsByProduct[$productId] = $optionCollection->appendSelections(
                $selectionCollection,
                $stripSelection,
                $this->catalogProduct->getSkipSaleableCheck()
            );
        }

        return $this->optionsByProduct[$productId];
    }
}
