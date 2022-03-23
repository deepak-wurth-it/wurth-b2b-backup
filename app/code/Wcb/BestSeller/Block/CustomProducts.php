<?php

namespace Wcb\BestSeller\Block;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Wcb\BestSeller\Helper\Data;

/**
 * Class CustomProducts
 * @package Wcb\BestSeller\Block
 */
class CustomProducts extends AbstractSlider
{

    /**
     * CustomProducts constructor.
     *
     * @param Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param Visibility $catalogProductVisibility
     * @param DateTime $dateTime
     * @param Data $helperData
     * @param HttpContext $httpContext
     * @param EncoderInterface $urlEncoder
     * @param Grouped $grouped
     * @param Configurable $configurable
     * @param LayoutFactory $layoutFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $productCollectionFactory,
        Visibility $catalogProductVisibility,
        DateTime $dateTime,
        Data $helperData,
        HttpContext $httpContext,
        EncoderInterface $urlEncoder,
        Grouped $grouped,
        Configurable $configurable,
        LayoutFactory $layoutFactory,
        array $data = []
    ) {

        parent::__construct(
            $context,
            $productCollectionFactory,
            $catalogProductVisibility,
            $dateTime,
            $helperData,
            $httpContext,
            $urlEncoder,
            $grouped,
            $configurable,
            $layoutFactory,
            $data
        );
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|mixed|null
     */
    public function getProductCollection()
    {
        $productIds = $this->getSlider()->getProductIds();
        $visibleProducts = $this->_catalogProductVisibility->getVisibleInCatalogIds();
        if (!is_array($productIds)) {
            $productIds = explode('&', $productIds);
        }

        if (empty($productIds)) {
            return null;
        }

        $collection = $this->_productCollectionFactory->create()
            ->addIdFilter($productIds)
            ->setPageSize($this->getProductsCount());

        $mpProductIds = $this->getProductParentIds($collection);

        $collection = $this->_productCollectionFactory->create()->addIdFilter($mpProductIds)->setVisibility($visibleProducts);
        $collection->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect('*')
            ->addStoreFilter($this->getStoreId())->setPageSize($this->getProductsCount());

        return $collection;
    }
}
