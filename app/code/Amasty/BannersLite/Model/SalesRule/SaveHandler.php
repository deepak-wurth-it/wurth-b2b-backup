<?php

namespace Amasty\BannersLite\Model\SalesRule;

use Amasty\BannersLite\Api\BannerRepositoryInterface;
use Amasty\BannersLite\Api\BannerRuleRepositoryInterface;
use Amasty\BannersLite\Api\Data\BannerInterface;
use Amasty\BannersLite\Api\Data\BannerRuleInterface;
use Amasty\BannersLite\Model\BannerFactory;
use Amasty\BannersLite\Model\BannerRuleFactory;
use Amasty\BannersLite\Model\Cache;
use Amasty\BannersLite\Model\ImageProcessor;
use Amasty\Base\Model\Serializer;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\SalesRule\Api\Data\RuleInterface as SalesRuleInterface;

/**
 * Sales Rule additional save handler
 * save image banner and banner rule data
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var BannerRepositoryInterface
     */
    private $bannerRepository;

    /**
     * @var BannerFactory
     */
    private $bannerFactory;

    /**
     * @var BannerRuleRepositoryInterface
     */
    private $bannerRuleRepository;

    /**
     * @var BannerRuleFactory
     */
    private $bannerRuleFactory;

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    /**
     * @var Serializer
     */
    private $serializerBase;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var Snapshot
     */
    private $snapshot;

    /**
     * Flag for flush product cache on banner rule save.
     *
     * @var bool
     */
    private $isBannerModified = false;

    public function __construct(
        BannerRepositoryInterface $bannerRepository,
        MetadataPool $metadataPool,
        BannerFactory $bannerFactory,
        BannerRuleRepositoryInterface $bannerRuleRepository,
        BannerRuleFactory $bannerRuleFactory,
        ImageProcessor $imageProcessor,
        Serializer $serializerBase,
        Cache $cache,
        Snapshot $snapshot
    ) {
        $this->bannerRepository = $bannerRepository;
        $this->metadataPool = $metadataPool;
        $this->bannerFactory = $bannerFactory;
        $this->bannerRuleRepository = $bannerRuleRepository;
        $this->bannerRuleFactory = $bannerRuleFactory;
        $this->imageProcessor = $imageProcessor;
        $this->serializerBase = $serializerBase;
        $this->cache = $cache;
        $this->snapshot = $snapshot;
    }

    /**
     * Stores Promo Banners value from Sales Rule extension attributes
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

        $this->isBannerModified = false;

        $this->saveBannerData($entity, $attributes);
        $this->saveBannerRule($entity, $attributes);

        return $entity;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $entity
     * @param array $attributes
     */
    private function saveBannerRule($entity, $attributes)
    {
        $linkField = $this->metadataPool->getMetadata(SalesRuleInterface::class)->getLinkField();
        $ruleLinkId = (int)$entity->getDataByKey($linkField);

        try {
            /** @var \Amasty\BannersLite\Model\BannerRule $bannerRule */
            $bannerRule = $this->bannerRuleRepository->getBySalesruleId($ruleLinkId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            /** @var \Amasty\BannersLite\Model\BannerRule $bannerRule */
            $bannerRule = $this->bannerRuleFactory->create();
        }

        if ($bannerRule->getId()) {
            $this->snapshot->registerSnapshot($bannerRule);
        }

        $this->convertCategoryIds($attributes);

        $bannerRule->addData($attributes);

        if (!isset($attributes[BannerRuleInterface::BANNER_PRODUCT_SKU]) && !$bannerRule->getBannerProductSku()) {
            $bannerRule->setBannerProductSku("");
        }

        if ((int)$bannerRule->getSalesruleId() !== $ruleLinkId) {
            $bannerRule->setEntityId(null);
            $bannerRule->setSalesruleId($ruleLinkId);
        }

        if ($isRuleModified = $this->snapshot->isModified($bannerRule)) {
            $this->bannerRuleRepository->save($bannerRule);
        }

        if ($this->isBannerModified || $isRuleModified) {
            $this->cache->cleanProductCache($bannerRule->getData());
        }
    }

    /**
     * @param array $attributes
     */
    private function convertCategoryIds(&$attributes)
    {
        if (isset($attributes[BannerRuleInterface::BANNER_PRODUCT_CATEGORIES])
            && is_array($attributes[BannerRuleInterface::BANNER_PRODUCT_CATEGORIES])
        ) {
            $attributes[BannerRuleInterface::BANNER_PRODUCT_CATEGORIES]
                = implode(',', $attributes[BannerRuleInterface::BANNER_PRODUCT_CATEGORIES]);
        } elseif (isset($attributes[BannerRuleInterface::SHOW_BANNER_FOR])
            && $attributes[BannerRuleInterface::SHOW_BANNER_FOR] == '2'
            && !isset($attributes[BannerRuleInterface::BANNER_PRODUCT_CATEGORIES])
        ) {
            $attributes[BannerRuleInterface::BANNER_PRODUCT_CATEGORIES] = '';
        } elseif (!isset($attributes[BannerRuleInterface::SHOW_BANNER_FOR])) {
            $attributes[BannerRuleInterface::SHOW_BANNER_FOR] = BannerRuleInterface::ALL_PRODUCTS;
        }
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $entity
     * @param array $attributes
     */
    private function saveBannerData(\Magento\SalesRule\Model\Rule $entity, &$attributes)
    {
        if (!isset($attributes[BannerInterface::EXTENSION_CODE])) {
            return;
        }
        $linkField = $this->metadataPool->getMetadata(SalesRuleInterface::class)->getLinkField();
        $ruleLinkId = (int)$entity->getDataByKey($linkField);
        $inputData = $attributes[BannerInterface::EXTENSION_CODE];
        unset($attributes[BannerInterface::EXTENSION_CODE]);

        /** @var array|BannerInterface $data */
        foreach ($inputData as $key => $data) {
            try {
                /** @var \Amasty\BannersLite\Model\Banner $promoBanner */
                $promoBanner = $this->bannerRepository->getByBannerType($ruleLinkId, $key);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                /** @var \Amasty\BannersLite\Model\Banner $promoBanner */
                $promoBanner = $this->bannerFactory->create();
            }
            $snapshotData = $promoBanner->getData();

            if ($data instanceof BannerInterface) {
                $data = $data->getData();
            }

            if (!$this->isEqualImage($promoBanner, $data)) {
                if ($promoBanner->getBannerImage()) {
                    //delete old banner
                    $this->imageProcessor->deleteImage($promoBanner->getBannerImage());
                    $promoBanner->setBannerImage(null);
                }
                $this->isBannerModified = true;
            }

            $promoBanner->addData($data);
            $promoBanner->setBannerType($key);

            if ((int)$promoBanner->getSalesruleId() !== $ruleLinkId) {
                $promoBanner->setEntityId(null);
                $promoBanner->setSalesruleId($ruleLinkId);
            }

            if (!$this->isBannerModified) {
                $this->isBannerModified = $this->isBannerModified($snapshotData, $promoBanner->getData());
            }

            if ($this->isBannerModified) {
                $this->bannerRepository->save($promoBanner);
            }
        }
    }

    /**
     * Compare images
     *
     * @param \Amasty\BannersLite\Model\Banner $promoBanner
     * @param array $newData
     *
     * @return bool
     */
    private function isEqualImage(\Amasty\BannersLite\Model\Banner $promoBanner, array $newData): bool
    {
        if (!$promoBanner->getBannerImage() xor !isset($newData[BannerInterface::BANNER_IMAGE])) {
            return false;
        }

        if (!$promoBanner->getBannerImage() && !isset($newData[BannerInterface::BANNER_IMAGE])) {
            return true;
        }

        $bannerImage = $this->serializerBase->unserialize($promoBanner->getBannerImage());
        if ($bannerImage) {
            $bannerImageName = $bannerImage[0]['name'];
            $newDataImage = $newData[BannerInterface::BANNER_IMAGE];
            if (is_string($newDataImage)) {
                //is json
                $newDataImage = $this->serializerBase->unserialize($newDataImage);
            }

            if ($newDataImage[0]['name'] !== $bannerImageName) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $snapshotData
     * @param array $promoBanner
     *
     * @return bool
     */
    private function isBannerModified(array $snapshotData, array $promoBanner): bool
    {
        unset($snapshotData[BannerInterface::BANNER_IMAGE], $promoBanner[BannerInterface::BANNER_IMAGE]);

        return $snapshotData != $promoBanner;
    }
}
