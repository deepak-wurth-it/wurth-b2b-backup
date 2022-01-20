<?php

namespace Amasty\BannersLite\Model;

class ConfigProvider extends \Amasty\Base\Model\ConfigProviderAbstract
{
    protected $pathPrefix = 'amasty_banners_lite/';

    const BANNERS_GROUP_GENERAL = 'general/';
    const ENABLE_TOP_BANNERS = 'enable_top_banner';
    const ENABLE_AFTER_BANNERS = 'enable_after_banner';
    const ENABLE_PRODUCT_BANNERS = 'enable_product_label';
    const ONE_BANNER = 'show_one_banner';

    /**
     * @return bool
     */
    public function isBannersEnabled()
    {
        return $this->isTopBannersEnabled() || $this->isAfterBannersEnabled() || $this->isProductBannersEnabled();
    }

    /**
     * @return bool
     */
    public function isTopBannersEnabled()
    {
        return (bool)$this->getValue(self::BANNERS_GROUP_GENERAL . self::ENABLE_TOP_BANNERS);
    }

    /**
     * @return bool
     */
    public function isAfterBannersEnabled()
    {
        return (bool)$this->getValue(self::BANNERS_GROUP_GENERAL . self::ENABLE_AFTER_BANNERS);
    }

    /**
     * @return bool
     */
    public function isProductBannersEnabled()
    {
        return (bool)$this->getValue(self::BANNERS_GROUP_GENERAL . self::ENABLE_PRODUCT_BANNERS);
    }

    /**
     * @return bool
     */
    public function isOneBannerEnabled()
    {
        return (bool)$this->getValue(self::BANNERS_GROUP_GENERAL . self::ONE_BANNER);
    }
}
