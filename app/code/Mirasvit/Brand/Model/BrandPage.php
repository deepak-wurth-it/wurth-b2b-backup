<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-navigation
 * @version   2.0.12
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Brand\Model;

use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Filesystem;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Mirasvit\Brand\Api\Data\BrandPageInterface;

class BrandPage extends AbstractModel implements BrandPageInterface
{
    private $imageUploader;

    private $filesystem;

    public function __construct(
        Filesystem $filesystem,
        Context $context,
        Registry $registry,
        ImageUploader $imageUploader,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );

        $this->imageUploader = $imageUploader;
        $this->filesystem    = $filesystem;
    }

    public function getId(): ?int
    {
        return $this->getData(self::ID) ? (int)$this->getData(self::ID) : null;
    }

    public function getAttributeOptionId(): int
    {
        return (int)$this->getData(self::ATTRIBUTE_OPTION_ID);
    }

    public function setAttributeOptionId(int $value): BrandPageInterface
    {
        return $this->setData(self::ATTRIBUTE_OPTION_ID, $value);
    }

    public function getAttributeId(): int
    {
        return (int)$this->getData(self::ATTRIBUTE_ID);
    }

    public function setAttributeId(int $value): BrandPageInterface
    {
        return $this->setData(self::ATTRIBUTE_ID, $value);
    }

    public function getIsActive(): bool
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    public function setIsActive(bool $value): BrandPageInterface
    {
        return $this->setData(self::IS_ACTIVE, $value);
    }

    public function getLogo(): string
    {
        return (string)$this->getData(self::LOGO);
    }

    public function setLogo(string $value): BrandPageInterface
    {
        return $this->setData(self::LOGO, $value);
    }

    public function getBrandTitle(): string
    {
        return (string)$this->getData(self::BRAND_TITLE);
    }

    public function setBrandTitle(string $value): BrandPageInterface
    {
        return $this->setData(self::BRAND_TITLE, $value);
    }

    public function getUrlKey(): string
    {
        return (string)$this->getData(self::URL_KEY);
    }

    public function setUrlKey(string $value): BrandPageInterface
    {
        return $this->setData(self::URL_KEY, $value);
    }

    public function getBrandDescription(): string
    {
        return (string)$this->getData(self::BRAND_DESCRIPTION);
    }


    public function setBrandDescription(string $value): BrandPageInterface
    {
        return $this->setData(self::BRAND_DESCRIPTION, $value);
    }

    public function getMetaTitle(): string
    {
        return (string)$this->getData(self::META_TITLE);
    }

    public function setMetaTitle(string $value): BrandPageInterface
    {
        return $this->setData(self::META_TITLE, $value);
    }

    public function getKeyword(): string
    {
        return (string)$this->getData(self::KEYWORD);
    }

    public function setKeyword(string $value): BrandPageInterface
    {
        return $this->setData(self::KEYWORD, $value);
    }

    public function getMetaDescription(): string
    {
        return (string)$this->getData(self::META_DESCRIPTION);
    }

    public function setMetaDescription(string $value): BrandPageInterface
    {
        return $this->setData(self::META_DESCRIPTION, $value);
    }

    public function getRobots(): string
    {
        return (string)$this->getData(self::ROBOTS);
    }

    public function setRobots(string $value): BrandPageInterface
    {
        return $this->setData(self::ROBOTS, $value);
    }

    public function getCanonical(): string
    {
        return (string)$this->getData(self::CANONICAL);
    }

    public function setCanonical(string $value): BrandPageInterface
    {
        return $this->setData(self::CANONICAL, $value);
    }

    public function getAttributeCode(): string
    {
        return (string)$this->getData(self::ATTRIBUTE_CODE);
    }

    public function getBrandName(): string
    {
        return (string)$this->getData(self::BRAND_NAME);
    }

    public function setBrandName(string $value): BrandPageInterface
    {
        return $this->setData(self::BRAND_NAME, $value);
    }


    public function getBannerAlt(): string
    {
        return (string)$this->getData(self::BANNER_ALT);
    }

    public function setBannerAlt(string $value): BrandPageInterface
    {
        return $this->setData(self::BANNER_ALT, $value);
    }

    public function getBannerTitle(): string
    {
        return (string)$this->getData(self::BANNER_TITLE);
    }

    public function setBannerTitle(string $value): BrandPageInterface
    {
        return $this->setData(self::BANNER_TITLE, $value);
    }

    public function getBanner(): string
    {
        return (string)$this->getData(self::BANNER);
    }

    public function setBanner(string $value): BrandPageInterface
    {
        return $this->setData(self::BANNER, $value);
    }

    public function getBannerPosition(): string
    {
        return (string)$this->getData(self::BANNER_POSITION);
    }

    public function setBannerPosition(string $value): BrandPageInterface
    {
        return $this->setData(self::BANNER_POSITION, $value);
    }

    public function getBrandShortDescription(): string
    {
        return (string)$this->getData(self::BRAND_SHORT_DESCRIPTION);
    }

    public function setBrandShortDescription(string $value): BrandPageInterface
    {
        return $this->setData(self::BRAND_SHORT_DESCRIPTION, $value);
    }

    public function afterSave(): self
    {
        $logo = $this->getLogo();
        $this->moveFileFromTmp($logo);
        $banner = $this->getBanner();
        $this->moveFileFromTmp($banner);

        return parent::afterSave();
    }

    protected function _construct(): void
    {
        $this->_init(ResourceModel\BrandPage::class);
    }

    private function moveFileFromTmp(string $image): void
    {
        $mediaDir = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        if ($image && !$mediaDir->isExist($this->imageUploader->getFilePath($this->imageUploader->getBasePath(), $image))
        ) {
            $this->imageUploader->moveFileFromTmp($image, true);
        }
    }
}
