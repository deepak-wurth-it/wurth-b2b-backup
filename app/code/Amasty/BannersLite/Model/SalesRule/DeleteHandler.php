<?php

namespace Amasty\BannersLite\Model\SalesRule;

use Amasty\BannersLite\Api\Data\BannerInterface;
use Amasty\BannersLite\Model\Cache;
use Amasty\BannersLite\Model\ImageProcessor;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class DeleteHandler
 */
class DeleteHandler implements ExtensionInterface
{
    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    public function __construct(Cache $cache, ImageProcessor $imageProcessor)
    {
        $this->cache = $cache;
        $this->imageProcessor = $imageProcessor;
    }

    /**
     * Delete Promo Banners value from tables and cleaning cache
     *
     * @param \Magento\SalesRule\Model\Rule|\Magento\SalesRule\Model\Data\Rule $entity
     * @param array $arguments
     *
     * @return \Magento\SalesRule\Model\Rule|\Magento\SalesRule\Model\Data\Rule
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        /** @var array $attributes */
        $attributes = $entity->getExtensionAttributes() ?: [];

        if (isset($attributes[BannerInterface::EXTENSION_CODE])) {
            $this->deleteImages($attributes);
            $this->cache->cleanProductCache($attributes);
        }

        return $entity;
    }

    /**
     * Delete banner images from media folder when rule was deleted
     *
     * @param array $attributes
     */
    private function deleteImages($attributes)
    {
        $banners = $attributes[BannerInterface::EXTENSION_CODE];

        /** @var \Amasty\BannersLite\Model\Banner $banner */
        foreach ($banners as $banner) {
            if(!is_array($banner)) {
                $this->imageProcessor->deleteImage($banner->getBannerImage());
            }
        }
    }
}
