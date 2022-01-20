<?php

namespace Amasty\BannersLite\Model;

use Amasty\Base\Model\Serializer;
use Amasty\BannersLite\Model\BannerImageUpload;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Store\Model\StoreManagerInterface;

class ImageProcessor
{
    /**
     * Banners area inside media folder
     */
    const BANNERS_MEDIA_PATH = 'amasty/banners_lite';

    /**
     * Banners temporary area inside media folder
     */
    const BANNERS_MEDIA_TMP_PATH = 'amasty/banners_lite/tmp';

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var BannerImageUpload
     */
    private $imageUploader;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Filesystem $filesystem,
        Serializer $serializer,
        BannerImageUpload $imageUploader,
        StoreManagerInterface $storeManager
    ) {
        $this->filesystem = $filesystem;
        $this->serializer = $serializer;
        $this->imageUploader = $imageUploader;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $imageName
     *
     * @return string string
     */
    public function getBannerImageUrl($imageName)
    {
        return $this->getBannerMedia($imageName) . DIRECTORY_SEPARATOR . $imageName;
    }

    /**
     * @param string $imageName
     *
     * @return string
     */
    public function moveFileFromTmp($imageName)
    {
        try {
            return $this->imageUploader->moveFileFromTmp($imageName, true);
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            // file already was moved from tmp
            return $imageName;
        }
    }

    /**
     * @param string $imageName
     *
     * @return string
     */
    public function copyFile($imageName)
    {
        try {
            return $this->imageUploader->duplicateFile($imageName);
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            // file already was duplicated
            return $imageName;
        }
    }

    /**
     * @param string $bannerImage
     */
    public function deleteImage($bannerImage)
    {
        $banner = $this->serializer->unserialize($bannerImage);

        if ($banner) {
            $this->getMediaDirectory()->delete(
                $this->getBannersRelativePath($banner[0]['name'])
            );
        }
    }

    /**
     * @param string $bannerName
     *
     * @return string
     */
    private function getBannersRelativePath($bannerName)
    {
        return self::BANNERS_MEDIA_PATH . DIRECTORY_SEPARATOR . $bannerName;
    }

    /**
     * @return WriteInterface
     */
    private function getMediaDirectory()
    {
        if ($this->mediaDirectory === null) {
            $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        }

        return $this->mediaDirectory;
    }

    /**
     * Url type http://url/pub/media/amasty/banners_lite
     *
     * @param string $imageName
     * @return string
     */
    private function getBannerMedia($imageName = '')
    {
        $bannerMedia = $this->storeManager
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        if (strpos($imageName, self::BANNERS_MEDIA_PATH) === false) {
            $bannerMedia .= self::BANNERS_MEDIA_PATH;
        }

        return $bannerMedia;
    }
}
