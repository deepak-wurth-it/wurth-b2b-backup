<?php

namespace Wcb\BestSeller\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Layout;
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

    /**
     * AddBlock constructor.
     *
     * @param RequestInterface $request
     * @param Data $helperData
     * @param ProductType $productType
     */
    public function __construct(
        RequestInterface $request,
        Data $helperData,
        ProductType $productType
    ) {
        $this->request = $request;
        $this->helperData = $helperData;
        $this->productType = $productType;
    }

    public function searchForId($id, $array) {
        foreach ($array as $key => $val) {
            if ($val['product_type'] === $id) {
                return $key;
            }
        }
        return null;
     }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        if (!$this->helperData->isEnabled()) {
            return $this;
        }

        $type = array_search($observer->getEvent()->getElementName(), [
            'content' => 'content',
            'sidebar' => 'catalog.leftnav'
        ]);
        if ($type !== false) {
            /** @var Layout $layout */
            $layout = $observer->getEvent()->getLayout();
            $fullActionName = $this->request->getFullActionName();
            $output = $observer->getTransport()->getOutput();
            
            $sliders = $this->helperData->getActiveSliders();
            $existsArray = [];
            $checkKeys = ['category', 'best-seller'];
            $removeKey = $skip = '';
            $product_type = array_column($sliders->getData(), 'product_type');

            if(empty(array_diff($checkKeys, $product_type))){
                $skip = 1;
                //$keyToFetch = $this->searchForId('category',$sliders);
                foreach ($sliders as $key => $val) {
                    if ($val['product_type'] === 'category') {
                        //return $key;
                        $slider = $val;
                    }
                }
                [$pageType, $location] = explode('.', 'cms_index_index.content-bottom');
                //$content = $layout->createBlock('Wcb\BestSeller\Block\CategoryProduct')->toHtml();
                $content = $layout->createBlock('Wcb\BestSeller\Block\CategoryProduct')->setTemplate('Wcb_BestSeller::categoryproduct.phtml')->setSlider($slider)->toHtml();        
                $output .= "<div class=\"custom-bpslider\" id=\"mageplaza-productslider-block-after-{$type}-cat-prod\">$content</div>";          
            }

                foreach ($sliders as $slider) {
                    [$pageType, $location] = explode('.', $slider->getLocation());
                    $product_types = $slider->getProductType();
                    if (($fullActionName == $pageType) || ($pageType == 'allpage')) {
                        if($skip == 1 && in_array($product_types, $checkKeys)){
                            continue;  
                        }
                        $content = $layout->createBlock($this->productType->getBlockMap($slider->getProductType()))
                            ->setSlider($slider)
                            ->toHtml();

                        if (strpos($location, $type) !== false) {
                            if (strpos($location, 'top') !== false) {
                                $output = "<div class=\"custom-bpslider\" id=\"mageplaza-productslider-block-before-{$type}-{$slider->getId()}\">$content</div>" . $output;
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
}
