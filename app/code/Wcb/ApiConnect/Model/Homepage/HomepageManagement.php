<?php
declare(strict_types=1);

namespace Wcb\ApiConnect\Model\Homepage;

use Exception;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Helper\Image;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Element\BlockFactory;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Plazathemes\Bannerslider\Model\ResourceModel\Banner\CollectionFactory as BannerCollection;
use Wcb\ApiConnect\Api\Homepage\HomepageManagementInterface;
use Wcb\BestSeller\Helper\Data as BestSellerHelper;
use Wcb\BestSeller\Model\Config\Source\ProductType;
use Wcb\Catalogslider\Model\ResourceModel\Catalogslider\CollectionFactory as CatalogsliderCollection;
use Wcb\Demonotices\Block\Demonotice;
use Wcb\PromotionBanner\Model\ResourceModel\PromotionBanner\CollectionFactory as PromotionBannerCollection;

class HomepageManagement implements HomepageManagementInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var Demonotice
     */
    protected $demoNotice;
    /**
     * @var JsonFactory
     */
    protected $jsonResultFactory;
    /**
     * @var BannerCollection
     */
    protected $bannerCollection;
    /**
     * @var DateTime
     */
    protected $dateTime;
    /**
     * @var PromotionBannerCollection
     */
    protected $promotionBanner;
    /**
     * @var CatalogsliderCollection
     */
    protected $catalogSlider;
    /**
     * @var BestSellerHelper
     */
    protected $bestSellerHelper;
    /**
     * @var BlockFactory
     */
    protected $blockFactory;
    /**
     * @var ProductType
     */
    protected $productType;
    /**
     * @var AbstractProduct
     */
    protected $abstractProduct;
    /**
     * @var Image
     */
    protected $helperImage;
    /**
     * @var Emulation
     */
    protected $appEmulation;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * HomepageManagement constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Demonotice $demoNotice
     * @param JsonFactory $jsonResultFactory
     * @param DateTime $dateTime
     * @param PromotionBannerCollection $promotionBanner
     * @param BannerCollection $bannerCollection
     * @param CatalogsliderCollection $catalogSlider
     * @param BestSellerHelper $bestSellerHelper
     * @param BlockFactory $blockFactory
     * @param ProductType $productType
     * @param AbstractProduct $abstractProduct
     * @param Image $helperImage
     * @param Emulation $appEmulation
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Demonotice $demoNotice,
        JsonFactory $jsonResultFactory,
        DateTime $dateTime,
        PromotionBannerCollection $promotionBanner,
        BannerCollection $bannerCollection,
        CatalogsliderCollection $catalogSlider,
        BestSellerHelper $bestSellerHelper,
        BlockFactory $blockFactory,
        ProductType $productType,
        AbstractProduct $abstractProduct,
        Image $helperImage,
        Emulation $appEmulation,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->demoNotice = $demoNotice;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->dateTime = $dateTime;
        $this->bannerCollection = $bannerCollection;
        $this->promotionBanner = $promotionBanner;
        $this->catalogSlider = $catalogSlider;
        $this->bestSellerHelper = $bestSellerHelper;
        $this->blockFactory = $blockFactory;
        $this->productType = $productType;
        $this->abstractProduct = $abstractProduct;
        $this->helperImage = $helperImage;
        $this->appEmulation = $appEmulation;
        $this->storeManager = $storeManager;
    }

    /**
     * @return Json
     */
    public function getHomePageInfo()
    {
        $result = [];
        try {
            $data = [];
            $data["offer_header"] = $this->demoNotice->getCustomDemoMessage();
            $data["homepage_slider"] = $this->getHomePageSlider();
            $data["promosition_banner"] = $this->getPromotionBanner();
            $data["catalog_slider"] = $this->getCatalogSlider();
            $data["bestseller_slider"] = $this->getBestSellerSlider();

            $result['success'] = true;
            $result['message'] = "Homepage data get successfully.";
            $result['data'] = $data;
        } catch (Exception $e) {
            $result['success'] = true;
            $result['message'] = $e->getMessage();
        }
        $resultData[] = $result;
        return $resultData;
    }

    /**
     * @return array
     */
    public function getHomePageSlider()
    {
        $currentDate = $this->dateTime->gmtDate();
        $sliderCollection = $this->bannerCollection
            ->create()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('display_pages', ["finset" => ["2"]])
            ->addFieldToFilter(
                ['valid_to', 'valid_to'],
                [['gteq' => $currentDate], ['null' => 'null']]
            )
            ->addFieldToFilter(
                ['valid_from', 'valid_from'],
                [['lteq' => $currentDate], ['null' => 'null']]
            );
        $sliderCollection->setOrderByBanner();
        return $sliderCollection->getData();
    }

    /**
     * @return array
     */
    public function getPromotionBanner()
    {
        $currentDate = $this->dateTime->gmtDate();
        $promotionBanner = $this->promotionBanner
            ->create()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter(
                ['valid_to', 'valid_to'],
                [['gteq' => $currentDate], ['null' => 'null']]
            )
            ->addFieldToFilter(
                ['valid_from', 'valid_from'],
                [['lteq' => $currentDate], ['null' => 'null']]
            )->setOrder('sort_order', 'ASC');
        return $promotionBanner->getData();
    }

    /**
     * @return array
     */
    public function getCatalogSlider()
    {
        $currentDate = $this->dateTime->gmtDate();
        $sliderCollection = $this->catalogSlider
            ->create()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter(
                ['valid_to', 'valid_to'],
                [['gteq' => $currentDate], ['null' => 'null']]
            )
            ->addFieldToFilter(
                ['valid_from', 'valid_from'],
                [['lteq' => $currentDate], ['null' => 'null']]
            )
            ->setOrder('sort_order', 'ASC');
        return $sliderCollection->getData();
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getBestSellerSlider()
    {
        // Store same page slider product collection ids
        $productsAndCategory = [];
        $categoriesIds = [];
        $productsIds = [];
        $fullActionName = "cms_index_index";
        $storeId = $this->storeManager->getStore()->getId();
        $this->appEmulation
            ->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);
        foreach ($this->bestSellerHelper->getActiveSliders() as $slider) {
            [$pageType, $location] = explode('.', $slider->getLocation());
            if ($fullActionName == $pageType || $pageType == 'allpage') {
                if ($slider->getProductType() === "category") {
                    $collectionData = $this->blockFactory->createBlock($this->productType->getBlockMap($slider->getProductType()))
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
                            "header_two" => $slider->getHeaderTwo(),
                            "detail" => "",
                            "type" => "category"
                        ];
                        $categoriesIds[] = $_category["id"];
                    }
                } else {
                    $collectionData = $this->blockFactory->createBlock($this->productType->getBlockMap($slider->getProductType()))
                        ->setSlider($slider)
                        ->getProductCollection();
                    foreach ($collectionData as $_product) {
                        if (in_array($_product->getId(), $productsIds)) {
                            continue;
                        }
                        $storeId = $this->storeManager->getStore()->getId();
                        
                        $productsAndCategory[] = [
                            "name" => $_product->getName(),
                            "image" => $this->helperImage->init($_product, 'product_base_image')->getUrl(),
                            "url" => $this->abstractProduct->getProductUrl($_product),
                            "offer" => $slider->getOffer(),
                            "header_two" => $slider->getHeaderTwo(),
                            "detail" => $this->abstractProduct->getProductDetailsHtml($_product),
                        ];

                        $productsIds[] = $_product->getId();
                    }
                }
            }
        }
        $this->appEmulation->stopEnvironmentEmulation();
        return $productsAndCategory;
    }
}
