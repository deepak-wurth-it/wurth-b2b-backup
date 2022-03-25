<?php

namespace Wcb\BestSeller\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Layout;
use Wcb\BestSeller\Block\AbstractSlider;
use Wcb\BestSeller\Helper\Data;
use Wcb\BestSeller\Model\Config\Source\ProductType;

/**
 * Class AddBlock
 * @package Mageplaza\AutoRelated\Observer
 */
class AddBlock implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var ProductType
     */
    protected $productType;

    protected $abstractSlider;

    /**
     * AddBlock constructor.
     *
     * @param RequestInterface $request
     * @param Data $helperData
     * @param ProductType $productType
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param AbstractSlider $abstractSlider
     */
    public function __construct(
        RequestInterface $request,
        Data $helperData,
        ProductType $productType,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        AbstractSlider $abstractSlider
    ) {
        $this->request = $request;
        $this->helperData = $helperData;
        $this->productType = $productType;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->abstractSlider = $abstractSlider;
    }

    public function searchForId($id, $array)
    {
        foreach ($array as $key => $val) {
            if ($val['product_type'] === $id) {
                return $key;
            }
        }
        return null;
    }
    public function execute(Observer $observer)
    {
        if (!$this->helperData->isEnabled()) {
            return $this;
        }

        $type = array_search($observer->getEvent()->getElementName(), [
            'content' => 'content',
            'sidebar' => 'catalog.leftnav',
            'custom-position' => 'set-slider-position',
            'footer-top' => 'footer-container'
        ]);
        if ($type !== false) {
            /** @var Layout $layout */
            $layout = $observer->getEvent()->getLayout();
            $fullActionName = $this->request->getFullActionName();

            $output = $observer->getTransport()->getOutput();

            // Store same page slider product collection ids

            $productsAndCategory = [];
            $categoriesIds = [];
            $productsIds = [];
            foreach ($this->helperData->getActiveSliders() as $slider) {
                [$pageType, $location] = explode('.', $slider->getLocation());
                if ($fullActionName == $pageType || $pageType == 'allpage') {
                    if ($slider->getProductType() === "category") {
                        $collectionData = $layout->createBlock($this->productType->getBlockMap($slider->getProductType()))
                            ->setSlider($slider)
                            ->getCategoryCollectionByIds();
                        foreach ($collectionData as $_category) {
                            if (in_array($_category["id"], $categoriesIds)) {
                                continue;
                            }
                            $productsAndCategory[] = [
                                "name" => $_category["name"],
                                "image" => $_category["image"],
                                "url" => $_category["url"],
                                "offer" => $slider->getOffer(),
                                "detail" => "",
                                "type" => "category"
                            ];
                            $categoriesIds[] = $_category["id"];
                        }
                    } else {
                        $collectionData = $layout->createBlock($this->productType->getBlockMap($slider->getProductType()))
                            ->setSlider($slider)
                            ->getProductCollection();
                        foreach ($collectionData as $_product) {
                            if (in_array($_product->getId(), $productsIds)) {
                                continue;
                            }
                            $productsAndCategory[] = [
                                "name" => $_product->getName(),
                                "image" => $this->abstractSlider->getImage($_product, 'recently_viewed_products_grid_content_widget')->toHtml(),
                                "url" => $this->abstractSlider->getProductUrl($_product),
                                "offer" => $slider->getOffer(),
                                "detail" => $this->abstractSlider->getProductDetailsHtml($_product),
                            ];
                            $productsIds[] = $_product->getId();
                        }
                    }
                }
            }

            $existsSliderInPage = [];
            foreach ($this->helperData->getActiveSliders() as $slider) {
                [$pageType, $location] = explode('.', $slider->getLocation());

                // Skip slider if already add in same page
                if (in_array($fullActionName, $existsSliderInPage)) {
                    continue;
                }

                if ($fullActionName == $pageType || $pageType == 'allpage') {
                    $existsSliderInPage[] = $fullActionName;
                    if (!empty($productsAndCategory)) {
                        $sliderTitle = "";
                        if ($fullActionName == 'cms_index_index' && $pageType != 'allpage') {
                            $sliderTitle = __("Best Sellers in Protupozarna zastita");
                        }
                        if ($fullActionName == 'wuerth_home_index' && $pageType != 'allpage') {
                            $sliderTitle = __('Now offer');
                        }

                        $content = $layout->createBlock($this->productType->getBlockMap($slider->getProductType()))
                            ->setSlider($slider)
                            ->setCustomTitle($sliderTitle)
                            ->setCustomizeCollection($productsAndCategory)
                            ->toHtml();
                    } else {
                        $content = $layout->createBlock($this->productType->getBlockMap($slider->getProductType()))
                            ->setSlider($slider)
                            ->toHtml();
                    }

                    if (strpos($location, $type) !== false) {
                        if (strpos($location, 'top') !== false) {
                            $output = "<div id=\"mageplaza-productslider-block-before-{$type}-{$slider->getId()}\">$content</div>" . $output;
                        } elseif (strpos($location, 'custom-position') !== false) {
                            $output = "<div id=\"mageplaza-productslider-block-before-{$type}-{$slider->getId()}\">$content</div>" . $output;
                        } else {
                            $output .= "<div class=\"custom-bpslider\" id=\"mageplaza-productslider-block-after-{$type}-{$slider->getId()}\">$content</div>";
                        }
                    }
                }
            }
            $observer->getTransport()->setOutput($output);
        }

        return $this;
    }
    public function getProductCollectionByIds($productIds)
    {
        $collection = $this->_productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('entity_id', ["in", $productIds]);

        return $collection;
    }
}
