<?php

namespace Amasty\BannersLite\Model\ResourceModel;

use Amasty\BannersLite\Api\Data\BannerInterface;
use Amasty\BannersLite\Model\ImageProcessor;
use Amasty\Base\Model\Serializer;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use \Magento\Framework\Model\ResourceModel\Db\Context;

class Banner extends AbstractDb
{
    const TABLE_NAME = 'amasty_banners_lite_banner_data';

    /**
     * @var Serializer
     */
    private $serializerBase;

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    public function __construct(
        Serializer $serializerBase,
        ImageProcessor $imageProcessor,
        Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->serializerBase = $serializerBase;
        $this->imageProcessor = $imageProcessor;
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, BannerInterface::ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    protected function _beforeSave(AbstractModel $object)
    {
        /** @var \Amasty\BannersLite\Model\Banner $object */
        $bannerImage = $object->getBannerImage();

        if ($this->isRuleCreated($bannerImage)) {
            $validName = $this->imageProcessor->moveFileFromTmp($bannerImage[0]['name']);
            $bannerImage = $this->getValidBannerImage($bannerImage, $validName);
        } elseif ($this->isRuleDuplicated($object, $bannerImage)) {
            $bannerImage = $this->serializerBase->unserialize($bannerImage);
            $validName = $this->imageProcessor->copyFile($bannerImage[0]['name']);
            $bannerImage = $this->getValidBannerImage($bannerImage, $validName);
        }

        $object->setBannerImage($bannerImage);

        return parent::_beforeSave($object);
    }

    /**
     * @param mixed $bannerImage
     *
     * @return bool
     */
    private function isRuleCreated($bannerImage)
    {
        return $bannerImage
            && is_array($bannerImage)
            && isset($bannerImage[0]['cookie']['upload'])
            && $bannerImage[0]['cookie']['upload'] !== "false";
    }

    /**
     * @param AbstractModel $object
     * @param mixed $bannerImage
     *
     * @return bool
     */
    private function isRuleDuplicated(AbstractModel $object, $bannerImage)
    {
        return $bannerImage && is_string($bannerImage) && $object->isObjectNew() === true;
    }

    /**
     * @param array $bannerImage
     * @param string $validName
     *
     * @return array
     */
    private function getValidBannerImage($bannerImage, $validName)
    {
        $bannerImage[0]['name'] = $validName;
        $bannerImage[0]['url'] = $this->imageProcessor->getBannerImageUrl($validName);

        return $this->serializerBase->serialize($bannerImage);
    }
}
