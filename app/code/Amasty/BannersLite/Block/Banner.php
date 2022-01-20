<?php

namespace Amasty\BannersLite\Block;

use Amasty\BannersLite\Api\Data\BannerInterface;
use Amasty\BannersLite\Model\ConfigProvider;
use Amasty\BannersLite\Model\ProductBannerProvider;
use Amasty\Base\Model\Serializer;
use Magento\Framework\View\Element\Template;

/**
 * @method string getPosition()
 */
class Banner extends Template
{
    /**
     * array with banners that showing on page by position (for setting 'Show One Banner Only')
     * @var array
     */
    private $showingBanners = [];

    /**
     * @var ProductBannerProvider
     */
    private $banners;

    /**
     * @var Serializer
     */
    private $serializerBase;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Template\Context $context,
        ProductBannerProvider $banners,
        Serializer $serializerBase,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->banners = $banners;
        $this->serializerBase = $serializerBase;
        $this->configProvider = $configProvider;
    }

    /**
     * @return array
     */
    public function getBanners()
    {
        if ($this->configProvider->isBannersEnabled()) {
            return $this->banners->getBanners($this->getProductId());
        }

        return [];
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        if (!$this->hasData('product_id')) {
            if (!empty($this->_request->getParam('product_id'))) {
                $this->setData('product_id', $this->_request->getParam('product_id'));
            } else {
                $this->setData('product_id', $this->_request->getParam('id'));
            }
        }

        return $this->_getData('product_id');
    }

    /**
     * @param array $banner
     *
     * @return string|null
     */
    public function getImage($banner)
    {
        $url = null;

        if ($banner[BannerInterface::BANNER_TYPE] == $this->getPosition()) {
            $image = $this->serializerBase->unserialize($banner[BannerInterface::BANNER_IMAGE]);
            if (is_array($image) && count($image) > 0 && $this->isOneBanner()) {
                $image = end($image);
                $url = $image['url'];
            }
        }

        return $url;
    }

    /**
     * @param array $banner
     *
     * @return string|null
     */
    public function getAlt($banner)
    {
        return $banner[BannerInterface::BANNER_TYPE] == $this->getPosition()
            ? $banner[BannerInterface::BANNER_ALT]
            : null;
    }

    /**
     * @param array $banner
     *
     * @return string|null
     */
    public function getHoverText($banner)
    {
        return $banner[BannerInterface::BANNER_TYPE] == $this->getPosition()
            ? $banner[BannerInterface::BANNER_HOVER_TEXT]
            : null;
    }

    /**
     * @param array $banner
     *
     * @return string|null
     */
    public function getLink($banner)
    {
        if ($banner[BannerInterface::BANNER_TYPE] == $this->getPosition()) {
            return $banner[BannerInterface::BANNER_LINK] ?: '#';
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isEnableBannerPosition()
    {
        $result = false;

        switch ($this->getPosition()) {
            case BannerInterface::TOP_BANNER:
                $result = $this->configProvider->isTopBannersEnabled();
                break;

            case BannerInterface::AFTER_BANNER:
                $result = $this->configProvider->isAfterBannersEnabled();
                break;

            case BannerInterface::PRODUCT_LABEL:
                $result = $this->configProvider->isProductBannersEnabled();
                break;
        }

        return $result;
    }

    /**
     * @return bool
     */
    private function isOneBanner()
    {
        if (!$this->configProvider->isOneBannerEnabled()) {
            return true;
        } elseif (array_key_exists($this->getPosition(), $this->showingBanners)) {
            return false;
        } else {
            $this->showingBanners[$this->getPosition()] = true;

            return true;
        }
    }
}
