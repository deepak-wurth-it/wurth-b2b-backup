<?php

namespace Wcb\BestSeller\Block;

/**
 * Class FeaturedProducts
 * @package Wcb\BestSeller\Block
 */
class FeaturedProducts extends AbstractSlider
{
    /**
     * get collection of feature products
     * @return mixed
     */
    public function getProductCollection()
    {
        $visibleProducts = $this->_catalogProductVisibility->getVisibleInCatalogIds();

        $collection = $this->_productCollectionFactory->create()->setVisibility($visibleProducts);
        $collection->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect('*')
            ->addStoreFilter($this->getStoreId())
            ->setPageSize($this->getProductsCount())
            ->addAttributeToFilter('is_featured', '1');

        return $collection;
    }
}
