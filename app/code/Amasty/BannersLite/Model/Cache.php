<?php

namespace Amasty\BannersLite\Model;

use Amasty\BannersLite\Api\Data\BannerRuleInterface;
use Amasty\BannersLite\Model\ResourceModel\CategoryProduct;
use Magento\CacheInvalidate\Model\PurgeCache;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\PageCache\Model\Config;

class Cache extends \Magento\Framework\Cache\Frontend\Decorator\TagScope
{
    /**
     * Cache type code unique among all cache types
     */
    const TYPE_IDENTIFIER = 'full_page';

    /**
     * Cache tag used to distinguish the cache type from all other cache
     */
    const CACHE_TAG = 'FPC';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var ResourceModel\CategoryProduct
     */
    private $categoryProduct;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var PurgeCache
     */
    private $purgeCache;

    public function __construct(
        CollectionFactory $collectionFactory,
        CategoryFactory $categoryFactory,
        CategoryProduct $categoryProduct,
        FrontendPool $cacheFrontendPool,
        Config $config,
        PurgeCache $purgeCache
    ) {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
        $this->collectionFactory = $collectionFactory;
        $this->categoryFactory = $categoryFactory;
        $this->categoryProduct = $categoryProduct;
        $this->config = $config;
        $this->purgeCache = $purgeCache;
    }

    /**
     * @param array $bannerRule
     */
    public function cleanProductCache($bannerRule)
    {
        $cacheTags = array_unique($this->getCacheTags($bannerRule));

        if ($cacheTags) {
            $this->clean(
                \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
                $cacheTags
            );
            if ($this->config->getType() == Config::VARNISH && $this->config->isEnabled()) {
                $this->purgeCache->sendPurgeRequest(implode('|', $cacheTags));
            }
        }
    }

    /**
     * @param array $bannerRule
     *
     * @return array
     */
    private function getCacheTags($bannerRule)
    {
        switch ($bannerRule[BannerRuleInterface::SHOW_BANNER_FOR]) {
            case BannerRuleInterface::PRODUCT_SKU:
                $cacheTags = $this->getProductCacheTags($bannerRule);
                break;

            case BannerRuleInterface::PRODUCT_CATEGORY:
                $cacheTags = $this->getCategoryCacheTags($bannerRule);
                break;

            case BannerRuleInterface::ALL_PRODUCTS:
                $cacheTags = [\Magento\Catalog\Model\Product::CACHE_TAG];
                break;

            default:
                $cacheTags = [];
                break;
        }

        return $cacheTags;
    }

    /**
     * @param array $bannerRule
     *
     * @return array
     */
    private function getProductCacheTags($bannerRule)
    {
        /** @var Collection $productCollection */
        $productCollection = $this->collectionFactory->create();

        $productCollection->addFieldToFilter(
            ProductInterface::SKU,
            ['in' => $bannerRule[BannerRuleInterface::BANNER_PRODUCT_SKU]]
        );

        return $this->getTagsByProductIds($productCollection->getAllIds());
    }

    /**
     * @param array $bannerRule
     *
     * @return array
     */
    private function getCategoryCacheTags($bannerRule)
    {
        return $this->getTagsByProductIds($this->categoryProduct->getProductIds($bannerRule));
    }

    /**
     * @param array $productIds
     *
     * @return array
     */
    private function getTagsByProductIds($productIds)
    {
        $cacheTags = [];

        foreach ($productIds as $productId) {
            $cacheTags[] = \Magento\Catalog\Model\Product::CACHE_TAG . '_' . $productId;
        }

        return $cacheTags;
    }
}
