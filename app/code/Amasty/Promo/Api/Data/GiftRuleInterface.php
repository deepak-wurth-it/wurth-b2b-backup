<?php

namespace Amasty\Promo\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface GiftRuleInterface extends ExtensibleDataInterface
{
    const RULE_NAME = 'ampromo_rule';
    const EXTENSION_CODE = self::RULE_NAME;

    /**#@+
     * Sales Rule Simple Action values
     */
    const SAME_PRODUCT = 'ampromo_product'; //Auto add the same product
    const PER_PRODUCT = 'ampromo_items'; //Auto add promo items with products
    const WHOLE_CART = 'ampromo_cart'; //Auto add promo items for the whole cart
    const SPENT = 'ampromo_spent'; //Auto add promo items for every $X spent
    const EACHN = 'ampromo_eachn'; //Add gift with each N-th product in the cart
    /**#@-*/

    /**#@+
     * Constants defined for keys of data array
     */
    const ENTITY_ID = 'entity_id';
    const SALESRULE_ID = 'salesrule_id';
    const SKU = 'sku';
    const TYPE = 'type';
    const TOP_BANNER_SHOW_GIFT_IMAGES = 'top_banner_show_gift_images';
    const AFTER_PRODUCT_BANNER_SHOW_GIFT_IMAGES = 'after_product_banner_show_gift_images';
    const ITEMS_DISCOUNT = 'items_discount';
    const MINIMAL_ITEMS_PRICE = 'minimal_items_price';
    const APPLY_TAX = 'apply_tax';
    const APPLY_SHIPPING = 'apply_shipping';
    /**#@-*/

    /**
     * @return string
     */
    public function getSku();

    /**
     * @param string $sku
     *
     * @return \Amasty\Promo\Api\Data\GiftRuleInterface
     */
    public function setSku($sku);

    /**
     * @return int
     */
    public function getType();

    /**
     * @param int $type
     *
     * @return \Amasty\Promo\Api\Data\GiftRuleInterface
     */
    public function setType($type);

    /**
     * @return int
     */
    public function getAfterProductBannerShowGiftImages();

    /**
     * @param int $afterProductBannerShowGiftImages
     *
     * @return \Amasty\Promo\Api\Data\GiftRuleInterface
     */
    public function setAfterProductBannerShowGiftImages($afterProductBannerShowGiftImages);

    /**
     * @return int
     */
    public function getTopBannerShowGiftImages();

    /**
     * @param int $topBannerShowGiftImages
     *
     * @return \Amasty\Promo\Api\Data\GiftRuleInterface
     */
    public function setTopBannerShowGiftImages($topBannerShowGiftImages);

    /**
     * @return string|null
     */
    public function getItemsDiscount();

    /**
     * @param string|null $itemsDiscount
     *
     * @return \Amasty\Promo\Api\Data\GiftRuleInterface
     */
    public function setItemsDiscount($itemsDiscount);

    /**
     * @return float|null
     */
    public function getMinimalItemsPrice();

    /**
     * @param float|null $minimalItemsPrice
     *
     * @return \Amasty\Promo\Api\Data\GiftRuleInterface
     */
    public function setMinimalItemsPrice($minimalItemsPrice);

    /**
     * @return int
     */
    public function getApplyTax();

    /**
     * @param int $applyTax
     *
     * @return \Amasty\Promo\Api\Data\GiftRuleInterface
     */
    public function setApplyTax($applyTax);

    /**
     * @return int
     */
    public function getApplyShipping();

    /**
     * @param int $applyShipping
     *
     * @return \Amasty\Promo\Api\Data\GiftRuleInterface
     */
    public function setApplyShipping($applyShipping);
}
