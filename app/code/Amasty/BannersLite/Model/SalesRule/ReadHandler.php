<?php

namespace Amasty\BannersLite\Model\SalesRule;

use Amasty\BannersLite\Api\BannerRepositoryInterface;
use Amasty\BannersLite\Api\BannerRuleRepositoryInterface;
use Amasty\BannersLite\Api\Data\BannerInterface;
use Amasty\BannersLite\Api\Data\BannerRuleInterface;
use Amasty\BannersLite\Model\BannerFactory;
use Amasty\BannersLite\Model\BannerRuleFactory;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\SalesRule\Api\Data\RuleInterface as SalesRuleInterface;

/**
 * Class ReadHandler
 */
class ReadHandler implements ExtensionInterface
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

    public function __construct(
        BannerRepositoryInterface $bannerRepository,
        MetadataPool $metadataPool,
        BannerFactory $bannerFactory,
        BannerRuleRepositoryInterface $bannerRuleRepository,
        BannerRuleFactory $bannerRuleFactory
    ) {
        $this->bannerRepository = $bannerRepository;
        $this->metadataPool = $metadataPool;
        $this->bannerFactory = $bannerFactory;
        $this->bannerRuleRepository = $bannerRuleRepository;
        $this->bannerRuleFactory = $bannerRuleFactory;
    }

    /**
     * Fill Sales Rule extension attributes with related Promo Banners
     *
     * @param \Magento\SalesRule\Model\Rule|\Magento\SalesRule\Model\Data\Rule $entity
     * @param array $arguments
     *
     * @return \Magento\SalesRule\Model\Rule|\Magento\SalesRule\Model\Data\Rule
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $linkField = $this->metadataPool->getMetadata(SalesRuleInterface::class)->getLinkField();
        $ruleLinkId = $entity->getDataByKey($linkField);

        if ($ruleLinkId) {
            /** @var array $attributes */
            $attributes = $entity->getExtensionAttributes() ?: [];

            $this->setBannersData($entity, $attributes, $ruleLinkId);
            $this->setBannerRule($entity, $attributes, $ruleLinkId);
        }

        return $entity;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $entity
     * @param array $attributes
     * @param int $ruleLinkId
     */
    private function setBannerRule(\Magento\SalesRule\Model\Rule $entity, &$attributes, $ruleLinkId)
    {
        try {
            /** @var \Amasty\BannersLite\Model\BannerRule $bannersRule */
            $bannersRule = $this->bannerRuleRepository->getBySalesruleId($ruleLinkId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            /** @var \Amasty\BannersLite\Model\BannerRule $bannersRule */
            $bannersRule = $this->bannerFactory->create();
        }

        $extAttributes = [
            BannerRuleInterface::SHOW_BANNER_FOR => $bannersRule->getShowBannerFor(),
            BannerRuleInterface::BANNER_PRODUCT_SKU => $bannersRule->getBannerProductSku(),
            BannerRuleInterface::BANNER_PRODUCT_CATEGORIES => $bannersRule->getBannerProductCategories(),
        ];

        $entity->setExtensionAttributes(array_merge($attributes, $extAttributes));
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $entity
     * @param array $attributes
     * @param int $ruleLinkId
     */
    private function setBannersData(\Magento\SalesRule\Model\Rule $entity, &$attributes, $ruleLinkId)
    {
        try {
            /** @var \Amasty\BannersLite\Model\Banner[] $promoBanners */
            $promoBanners = $this->bannerRepository->getBySalesruleId($ruleLinkId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            /** @var \Amasty\BannersLite\Model\Banner $promoBanners */
            $promoBanners = $this->bannerFactory->create();
        }

        $attributes[BannerInterface::EXTENSION_CODE] = $promoBanners;
        $entity->setExtensionAttributes($attributes);
    }
}
