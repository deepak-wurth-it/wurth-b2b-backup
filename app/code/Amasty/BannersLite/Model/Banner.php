<?php

namespace Amasty\BannersLite\Model;

use Amasty\BannersLite\Api\Data\BannerInterface;

class Banner extends \Magento\Framework\Model\AbstractModel implements BannerInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\BannersLite\Model\ResourceModel\Banner::class);
        $this->setIdFieldName(\Amasty\BannersLite\Api\Data\BannerInterface::ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function getSalesruleId()
    {
        return $this->_getData(BannerInterface::SALESRULE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setSalesruleId($salesruleId)
    {
        $this->setData(BannerInterface::SALESRULE_ID, $salesruleId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBannerImage()
    {
        return $this->_getData(BannerInterface::BANNER_IMAGE);
    }

    /**
     * @inheritdoc
     */
    public function setBannerImage($bannerImage)
    {
        $this->setData(BannerInterface::BANNER_IMAGE, $bannerImage);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBannerAlt()
    {
        return $this->_getData(BannerInterface::BANNER_ALT);
    }

    /**
     * @inheritdoc
     */
    public function setBannerAlt($bannerAlt)
    {
        $this->setData(BannerInterface::BANNER_ALT, $bannerAlt);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBannerHoverText()
    {
        return $this->_getData(BannerInterface::BANNER_HOVER_TEXT);
    }

    /**
     * @inheritdoc
     */
    public function setBannerHoverText($bannerHoverText)
    {
        $this->setData(BannerInterface::BANNER_HOVER_TEXT, $bannerHoverText);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBannerLink()
    {
        return $this->_getData(BannerInterface::BANNER_LINK);
    }

    /**
     * @inheritdoc
     */
    public function setBannerLink($bannerLink)
    {
        $this->setData(BannerInterface::BANNER_LINK, $bannerLink);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBannerType()
    {
        return $this->_getData(BannerInterface::BANNER_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setBannerType($bannerType)
    {
        $this->setData(BannerInterface::BANNER_TYPE, $bannerType);

        return $this;
    }
}
