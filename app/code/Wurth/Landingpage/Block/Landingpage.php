<?php

namespace Wurth\Landingpage\Block;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Cms\Model\Page;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Wurth\Landingpage\Model\ResourceModel\LandingPage\CollectionFactory as LandingPageCollectionFactory;

class Landingpage extends Template
{
    /**
     * @var Context
     */
    protected $context;
    /**
     * @var Http
     */
    protected $request;
    /**
     * @var Page
     */
    protected $_page;
    /**
     * @var LandingPageCollectionFactory
     */
    protected $landigPageCollection;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;
    /**
     * @var CollectionFactory
     */
    protected $_categoryCollectionFactory;
    /**
     * @var Image
     */
    protected $_helperImage;
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Landingpage constructor.
     * @param Context $context
     * @param Http $request
     * @param Page $page
     * @param LandingPageCollectionFactory $landingPageCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param CollectionFactory $categoryCollectionFactory
     * @param Image $helperImage
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        Http $request,
        Page $page,
        LandingPageCollectionFactory $landingPageCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        CollectionFactory $categoryCollectionFactory,
        Image $helperImage,
        StoreManagerInterface $storeManager
    ) {
        $this->context = $context;
        $this->request = $request;
        $this->landigPageCollection = $landingPageCollectionFactory;
        $this->_page = $page;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_helperImage = $helperImage;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Get Landing Page Products
     *
     * @return array|array[]
     */
    public function getProducts()
    {
        $cmsPageId = $this->getCmsPageId();
        $data = [];
        if ($cmsPageId) {
            $landingProducts = $this->landigPageCollection->create()
                ->addFieldToFilter("cms_page", ["eq", $cmsPageId])
                ->getFirstItem();

            if ($landingProducts && $landingProducts->getProductId()) {
                $products = json_decode($landingProducts->getProductId(), true);
                $productsIds = array_keys($products);
                $data = $this->getProductCollection($productsIds);
            }
        }

        return $data;
    }

    /**
     * Get current cms page
     *
     * @return bool|int
     */
    public function getCmsPageId()
    {
        $actionName = $this->request->getFullActionName();
        if ($actionName === "cms_page_view") {
            if ($this->_page->getId()) {
                return $this->_page->getId();
            }
        }
        return false;
    }

    /**
     * Get Product Collection
     *
     * @param $productsIds
     * @return array|array[]
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getProductCollection($productsIds)
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('entity_id', ["in" => $productsIds]);

        $categoryData = [];
        $productData = [];
        foreach ($collection as $_product) {
            $proCats = $_product->getCategoryIds();

            $fifthLevelCategory = $this->_categoryCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter("entity_id", ["in" => $proCats]);

            $fourthLevelCat = $this->getFourthLevelCategory($fifthLevelCategory);

            if (!empty($fourthLevelCat)) {
                $fourthLevelcategoryCollection = $this->_categoryCollectionFactory->create()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter("pim_category_id", ["in" => $fourthLevelCat]);

                foreach ($fourthLevelcategoryCollection as $category) {
                    /*$image_url = $this->_storeManager->getStore()
                            ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                        . "catalog/product/" . $_product->getImage();*/
                    $image_url = $this->_helperImage
                        ->init($_product, 'product_page_main_image')->getUrl();
                    $productInfo = [];
                    $productInfo["product_id"] = $_product->getId();
                    $productInfo["name"] = $_product->getName();
                    $productInfo["image"] = $image_url;
                    $productInfo["product_url"] = $_product->getProductUrl();
                    $productInfo["category_id"] = $category->getId();
                    $productData[$category->getId()][$_product->getId()] = $productInfo;
                    $categoryData[$category->getId()] = $category->getName();
                }
            }
        }
        if (!empty($categoryData) && !empty($productData)) {
            $result = [
                "category_data" => $categoryData,
                "product_data" => $productData,
            ];
        } else {
            $result = [];
        }

        return $result;
    }

    /**
     * Gt Fourth level category ids
     *
     * @param $fifthLevelCategory
     * @return array
     */
    public function getFourthLevelCategory($fifthLevelCategory)
    {
        $fourthLevelCat = [];
        foreach ($fifthLevelCategory as $category) {
            $fourthLevelCat[] = $category->getPimCategoryParentId();
        }
        return $fourthLevelCat;
    }
}
