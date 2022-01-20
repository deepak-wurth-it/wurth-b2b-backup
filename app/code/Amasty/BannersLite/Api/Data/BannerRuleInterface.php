<?php

namespace Amasty\BannersLite\Api\Data;

interface BannerRuleInterface
{
    const ALL_PRODUCTS = 0;
    const PRODUCT_SKU = 1;
    const PRODUCT_CATEGORY = 2;

    /**#@+
     * Constants defined for keys of data array
     */
    const ENTITY_ID = 'entity_id';
    const SALESRULE_ID = 'salesrule_id';
    const BANNER_PRODUCT_SKU = 'banner_product_sku';
    const BANNER_PRODUCT_CATEGORIES = 'banner_product_categories';
    const SHOW_BANNER_FOR = 'show_banner_for';
    /**#@-*/

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param int $entityId
     *
     * @return \Amasty\BannersLite\Api\Data\BannerRuleInterface
     */
    public function setEntityId($entityId);

    /**
     * @return int
     */
    public function getSalesruleId();

    /**
     * @param int $salesruleId
     *
     * @return \Amasty\BannersLite\Api\Data\BannerRuleInterface
     */
    public function setSalesruleId($salesruleId);

    /**
     * @return string|null
     */
    public function getBannerProductSku();

    /**
     * @param string|null $bannerProductSku
     *
     * @return \Amasty\BannersLite\Api\Data\BannerRuleInterface
     */
    public function setBannerProductSku($bannerProductSku);

    /**
     * @return array|null
     */
    public function getBannerProductCategories();

    /**
     * @param string|null $bannerProductCategories
     *
     * @return \Amasty\BannersLite\Api\Data\BannerRuleInterface
     */
    public function setBannerProductCategories($bannerProductCategories);

    /**
     * @return int|null
     */
    public function getShowBannerFor();

    /**
     * @param int|null $showBannerFor
     *
     * @return \Amasty\BannersLite\Api\Data\BannerRuleInterface
     */
    public function setShowBannerFor($showBannerFor);
}
