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
use Magento\Reports\Block\Product\Viewed as ReportProductViewed;
use Wcb\BestSeller\Helper\Data;

/**
 * Class RecentProducts
 * @package Wcb\BestSeller\Block
 */
class RecentProducts extends AbstractSlider
{
    /**
     * @var ReportProductViewed
     */
    protected $reportProductViewed;

    /**
     * RecentProducts constructor.
     *
     * @param Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param Visibility $catalogProductVisibility
     * @param DateTime $dateTime
     * @param Data $helperData
     * @param HttpContext $httpContext
     * @param EncoderInterface $urlEncoder
     * @param ReportProductViewed $reportProductViewed
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
        ReportProductViewed $reportProductViewed,
        Grouped $grouped,
        Configurable $configurable,
        LayoutFactory $layoutFactory,
        array $data = []
    ) {
        $this->reportProductViewed = $reportProductViewed;

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
     * Get Collection Recently Viewed product
     * @return mixed
     */
    public function getProductCollection()
    {
        return $this->reportProductViewed->getItemsCollection()->setPageSize($this->getProductsCount());
    }
}
