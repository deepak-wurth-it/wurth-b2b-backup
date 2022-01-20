<?php

namespace Amasty\Promo\Model;

use Amasty\BannersLite\Api\BannerRepositoryInterface;
use Amasty\BannersLite\Api\BannerRuleRepositoryInterface;
use Amasty\BannersLite\Api\Data\BannerInterface;
use Amasty\BannersLite\Model\Banner;
use Amasty\BannersLite\Model\BannerImageUpload;
use Amasty\BannersLite\Model\BannerRule;
use Amasty\BannersLite\Model\ImageProcessor;
use Amasty\Base\Model\Serializer;
use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * Upgrade script
 */
class ImageMigrate
{
    const BASE_TMP_DIRECTORY = 'amasty_promo/tmp/banner';

    /**
     * @var string
     */
    private $mediaPath = '';

    /**
     * Loaded banners model grouped by sales rule id.
     *
     * @var Banner[]
     */
    private $loadedBanners = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var BannerImageUpload
     */
    private $bannerImageUpload;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var BannerRepositoryInterface
     */
    private $bannerRepository;

    /**
     * @var ResourceModel\Rule\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var BannerRuleRepositoryInterface
     */
    private $bannerRuleRepository;

    public function __construct(
        LoggerInterface $logger,
        BannerImageUpload $bannerImageUpload,
        Serializer $serializer,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        BannerRepositoryInterface $bannerRepository,
        BannerRuleRepositoryInterface $bannerRuleRepository,
        ResourceModel\Rule\CollectionFactory $collectionFactory
    ) {
        $this->logger = $logger;
        $this->bannerImageUpload = $bannerImageUpload;
        $this->serializer = $serializer;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->bannerRepository = $bannerRepository;
        $this->bannerRuleRepository = $bannerRuleRepository;
        $this->collectionFactory = $collectionFactory;
    }

    public function execute()
    {
        /** @var Rule[] $rules */
        $rules = $this->collectionFactory->create()->getItems();
        $this->bannerImageUpload->setBaseTmpPath(static::BASE_TMP_DIRECTORY);
        $this->mediaPath = $this->storeManager->getStore()
                ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $this->bannerImageUpload->getBasePath();

        foreach ($rules as $rule) {
            $this->saveBannerData(
                BannerInterface::TOP_BANNER,
                $rule->getData('top_banner_image'),
                $rule->getData('top_banner_alt'),
                $rule->getData('top_banner_on_hover_text'),
                $rule->getData('top_banner_link'),
                $rule->getSalesruleId()
            );
            $this->saveBannerData(
                BannerInterface::AFTER_BANNER,
                $rule->getData('after_product_banner_image'),
                $rule->getData('after_product_banner_alt'),
                $rule->getData('after_product_banner_on_hover_text'),
                $rule->getData('after_product_banner_link'),
                $rule->getSalesruleId()
            );
            $this->saveBannerData(
                BannerInterface::PRODUCT_LABEL,
                $rule->getData('label_image'),
                $rule->getData('label_image_alt'),
                null,
                null,
                $rule->getSalesruleId()
            );
        }
    }

    /**
     * @param int $type
     * @param string $image
     * @param string $alt
     * @param string $hover
     * @param string $link
     * @param int $salesRuleId
     *
     * @return bool
     * @throws LocalizedException
     */
    private function saveBannerData($type, $image, $alt, $hover, $link, $salesRuleId)
    {
        if (!($image || $alt || $hover || $link)) {
            return false;
        }

        if (!($bannerImage = $this->moveImage($image))) {
            return false;
        }

        try {
            /** @var BannerRule $bannerRuleModel */
            $bannerRuleModel = $this->bannerRuleRepository->getBySalesruleId($salesRuleId);
        } catch (NoSuchEntityException $exception) {
            /** @var BannerRule $bannerRuleModel */
            $bannerRuleModel = $this->bannerRuleRepository->getEmptyModel();
        }

        $bannerRuleModel->setSalesruleId($salesRuleId);
        $bannerRuleModel->setShowBannerFor(0);

        $bannerModel = $this->getBannerBySalesRule($salesRuleId, $type);

        $bannerModel
            ->setBannerType($type)
            ->setBannerImage($bannerImage)
            ->setBannerAlt($alt)
            ->setBannerHoverText($hover)
            ->setBannerLink($link)
            ->setSalesruleId($salesRuleId);

        try {
            $this->bannerRuleRepository->save($bannerRuleModel);
            $this->bannerRepository->save($bannerModel);
        } catch (CouldNotSaveException $exception) {
            $this->logger->critical($exception);

            throw new LocalizedException(
                __(
                    'Could not migrate banners data. Exception message: %1. For details see exception log.',
                    $exception->getMessage()
                )
            );
        }

        return true;
    }

    /**
     * @param int $ruleId
     * @param int $bannerType
     *
     * @return Banner
     */
    private function getBannerBySalesRule($ruleId, $bannerType)
    {
        $bannerModel = false;

        try {
            if (!isset($this->loadedBanners[$ruleId])) {
                $this->loadedBanners[$ruleId] = $this->bannerRepository->getBySalesruleId($ruleId);
            }

            foreach ($this->loadedBanners[$ruleId] as $promoBanner) {
                if ($promoBanner->getBannerType() == $bannerType) {
                    $bannerModel = $promoBanner;
                    break;
                }
            }
        } catch (NoSuchEntityException $exception) {
            $bannerModel = false;
        }
        if (!$bannerModel) {
            $bannerModel = $this->bannerRepository->getEmptyModel();
            $this->loadedBanners[$ruleId][] = $bannerModel;
        }

        return $bannerModel;
    }

    /**
     * Move image to Banners Lite folder.
     * If exception is caught continue without image data.
     *
     * @param string $imageSerialized
     *
     * @return string|false
     */
    private function moveImage($imageSerialized)
    {
        $imageData = $this->serializer->unserialize($imageSerialized);

        if (!$imageData) {
            return false;
        }

        if (is_array(current($imageData))) {
            $imageData = current($imageData);
        }

        try {
            if ($this->whetherToMoveFile($imageData['name'])) {
                $this->bannerImageUpload->moveFileFromTmp($imageData['name']);
            }
            $imageData['cookie']['upload'] = true;
            $imageData['url'] = $this->mediaPath . DIRECTORY_SEPARATOR . $imageData['name'];
        } catch (Exception $exception) {
            $this->logger->critical($exception);

            return false;
        }

        $imageSerialized = $this->serializer->serialize([$imageData]);

        return $imageSerialized;
    }

    /**
     * @param string $filename
     *
     * @return bool
     *
     * @throws FileNotFoundException
     */
    private function whetherToMoveFile($filename)
    {
        //check file exist in Amasty_Promo banners folder
        $isExist = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->isExist(
            $this->bannerImageUpload->getFilePath(self::BASE_TMP_DIRECTORY, $filename)
        );

        //check file exists in Amasty_BannersLite folder
        $haveSameFile = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->isExist(
            $this->bannerImageUpload->getFilePath(ImageProcessor::BANNERS_MEDIA_PATH, $filename)
        );

        if (!($isExist || $haveSameFile)) {
            throw new FileNotFoundException('Could not find original banner file to migrate');
        }

        return $isExist && !$haveSameFile;
    }
}
