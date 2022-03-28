<?php

namespace Wcb\BestSeller\Block;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Wcb\BestSeller\Helper\Data;
use Wcb\BestSeller\Block\CategoryId;
use Wcb\BestSeller\Block\BestSellerProducts;
/**
 * Class CategoryProduct
 * @package Wcb\BestSeller\Block
 */
class CategoryProduct extends AbstractSlider
{
    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    protected $categoryId;
    protected $bestSellerProducts;

    /**
     * CategoryProduct constructor.
     *
     * @param Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param Visibility $catalogProductVisibility
     * @param DateTime $dateTime
     * @param Data $helperData
     * @param HttpContext $httpContext
     * @param EncoderInterface $urlEncoder
     * @param CategoryFactory $categoryFactory
     * @param Grouped $grouped
     * @param Configurable $configurable
     * @param LayoutFactory $layoutFactory
     * @param PriceCurrencyInterface $priceCurrencyInterface
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection
     * @param \Wcb\BestSeller\Block\CategoryId $categoryId
     * @param \Wcb\BestSeller\Block\BestSellerProducts $bestSellerProducts
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
        CategoryFactory $categoryFactory,
        Grouped $grouped,
        Configurable $configurable,
        LayoutFactory $layoutFactory,
        PriceCurrencyInterface $priceCurrencyInterface,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection,
        \Wcb\BestSeller\Block\CategoryId $categoryId,
        \Wcb\BestSeller\Block\BestSellerProducts $bestSellerProducts,
        array $data = []
    ) {
        $this->_categoryFactory = $categoryFactory;
        $this->categoryCollection = $categoryCollection;
        $this->categoryId = $categoryId;
        $this->bestSellerProducts = $bestSellerProducts;

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
            $priceCurrencyInterface,
            $data
        );
    }

    /**
     * Get Product Collection by Category Ids
     *
     * @return $this|array
     */
    public function getProductCollection()
    {
        $collection = $productId = $categoryArray = [];[];
        $collection['product'] = $this->bestSellerProducts->getProductCollection();
        $categoryIds = $this->getSliderCategoryIds();
        if (is_array($categoryIds)) {
            foreach($categoryIds as $cat)
            {
                $getCategory = $this->_categoryFactory->create()->load($cat)->getData();
                array_push($categoryArray, $getCategory);
            }
        }
        $collection['category'] = $categoryArray;
        return $collection;
    }

    public function getCategoryCollection()
    {
        $catIds = $this->getSliderCategoryIds();

        // return $this->categoryCollection->create()
        // ->addAttributeToSelect('*')
        // // ->addAttributeToSelect('thumb_nail')
        // // ->addAttributeToSelect('url_path')
        // ->addFieldToFilter('entity_id', $catIds)
        // ;

// foreach ($categoryCollection->getItems() as $category) {
//     /** @var \Magento\Catalog\Model\Category\Interceptor $category */

//     // get the category data
//     echo "<pre>";
//     var_dump($category->getData());
// }


        if (is_array($catIds)) {
            $productId = $categoryArray = [];
            foreach($catIds as $cat)
            {
                $getCategory = $this->_categoryFactory->create()->load($cat)->getData();
                array_push($categoryArray, $getCategory);
            }
            return $categoryArray;
        }
    }

    /**
     * Get ProductIds by Category
     *
     * @return array
     */
    public function getProductIdsByCategory()
    {
        $productIds = [];
        $catIds     = $this->getSliderCategoryIds();

        if (is_array($catIds)) {
            $productId = [];

            foreach($catIds as $cat)
            {
                $collection = $this->_productCollectionFactory->create();
                $category = $this->_categoryFactory->create()->load($cat);
                $collection->addAttributeToSelect('*')->addCategoryFilter($category);

                foreach ($collection as $item) {
                    $productId[] = $item->getData('entity_id');
                }

                $productIds = array_merge($productIds, $productId);
            }
        }else {
            $collection = $this->_productCollectionFactory->create();
            $category = $this->_categoryFactory->create()->load($catIds);
            $collection->addAttributeToSelect('*')->addCategoryFilter($category);

            foreach ($collection as $item) {
                $productIds[] = $item->getData('entity_id');
            }
        }

        $keys = array_keys($productIds);
        shuffle($keys);
        $productIdsRandom = [];

        foreach ($keys as $key => $value) {
            $productIdsRandom[] = $productIds[$value];

            if ($key >= ($this->getProductsCount() - 1)) {
                break;
            }
        }

        return $productIdsRandom;
    }

    /**
     * Get Slider CategoryIds
     *
     * @return array|int|mixed
     */
    public function getSliderCategoryIds()
    {
        if ($this->getData('category_id')) {
            return $this->getData('category_id');
        }
        if ($this->getSlider()) {
            return explode(',', $this->getSlider()->getCategoriesIds());
        }

        return 2;
    }
}
