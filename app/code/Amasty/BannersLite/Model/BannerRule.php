<?php

namespace Amasty\BannersLite\Model;

use \Amasty\BannersLite\Api\Data\BannerRuleInterface;

class BannerRule extends \Magento\Framework\Model\AbstractModel implements BannerRuleInterface
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\BannersLite\Model\ResourceModel\BannerRule::class);
        $this->setIdFieldName(\Amasty\BannersLite\Api\Data\BannerRuleInterface::ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function getBannerProductCategories()
    {
        $cats = $this->_getData(BannerRuleInterface::BANNER_PRODUCT_CATEGORIES);

        if ($cats) {
            return explode(',', $cats);
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getSalesruleId()
    {
        return $this->_getData(BannerRuleInterface::SALESRULE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setSalesruleId($salesruleId)
    {
        $this->setData(BannerRuleInterface::SALESRULE_ID, $salesruleId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBannerProductSku()
    {
        return $this->_getData(BannerRuleInterface::BANNER_PRODUCT_SKU);
    }

    /**
     * @inheritdoc
     */
    public function setBannerProductSku($bannerProductSku)
    {
        $this->setData(BannerRuleInterface::BANNER_PRODUCT_SKU, $bannerProductSku);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setBannerProductCategories($bannerProductCategories)
    {
        $this->setData(BannerRuleInterface::BANNER_PRODUCT_CATEGORIES, $bannerProductCategories);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getShowBannerFor()
    {
        return $this->_getData(BannerRuleInterface::SHOW_BANNER_FOR);
    }

    /**
     * @inheritdoc
     */
    public function setShowBannerFor($showBannerFor)
    {
        $this->setData(BannerRuleInterface::SHOW_BANNER_FOR, $showBannerFor);

        return $this;
    }
}
