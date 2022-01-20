<?php
namespace Amasty\Promo\Model;

use Amasty\Promo\Api\Data\GiftRuleInterface;

class Rule extends \Magento\Framework\Model\AbstractModel implements GiftRuleInterface
{
    const RULE_TYPE_ALL = 0;
    const RULE_TYPE_ONE = 1;

    const NOT_AUTO_FREE_ITEMS = 0;
    const AUTO_FREE_ITEMS = 1;
    const AUTO_FREE_DISCOUNTED_ITEMS = 2;

    const AFTER_DISCOUNTS = 1;
    const BEFORE_DISCOUNTS = 0;

    const OPTION_ID = 'ampromo_rule_id';

    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\Promo\Model\ResourceModel\Rule::class);
        $this->setIdFieldName(self::ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function getEntityId()
    {
        return $this->_getData(GiftRuleInterface::ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setEntityId($entityId)
    {
        $this->setData(GiftRuleInterface::ENTITY_ID, $entityId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSalesruleId()
    {
        return $this->_getData(GiftRuleInterface::SALESRULE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setSalesruleId($salesruleId)
    {
        $this->setData(GiftRuleInterface::SALESRULE_ID, $salesruleId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSku()
    {
        return $this->_getData(GiftRuleInterface::SKU);
    }

    /**
     * @inheritdoc
     */
    public function setSku($sku)
    {
        $this->setData(GiftRuleInterface::SKU, $sku);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->_getData(GiftRuleInterface::TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        $this->setData(GiftRuleInterface::TYPE, $type);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAfterProductBannerShowGiftImages()
    {
        return $this->_getData(GiftRuleInterface::AFTER_PRODUCT_BANNER_SHOW_GIFT_IMAGES);
    }

    /**
     * @inheritdoc
     */
    public function setAfterProductBannerShowGiftImages($afterProductBannerShowGiftImages)
    {
        $this->setData(GiftRuleInterface::AFTER_PRODUCT_BANNER_SHOW_GIFT_IMAGES, $afterProductBannerShowGiftImages);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTopBannerShowGiftImages()
    {
        return $this->_getData(GiftRuleInterface::TOP_BANNER_SHOW_GIFT_IMAGES);
    }

    /**
     * @inheritdoc
     */
    public function setTopBannerShowGiftImages($topBannerShowGiftImages)
    {
        $this->setData(GiftRuleInterface::TOP_BANNER_SHOW_GIFT_IMAGES, $topBannerShowGiftImages);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getItemsDiscount()
    {
        return $this->_getData(GiftRuleInterface::ITEMS_DISCOUNT);
    }

    /**
     * @inheritdoc
     */
    public function setItemsDiscount($itemsDiscount)
    {
        $this->setData(GiftRuleInterface::ITEMS_DISCOUNT, $itemsDiscount);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMinimalItemsPrice()
    {
        return $this->_getData(GiftRuleInterface::MINIMAL_ITEMS_PRICE);
    }

    /**
     * @inheritdoc
     */
    public function setMinimalItemsPrice($minimalItemsPrice)
    {
        $this->setData(GiftRuleInterface::MINIMAL_ITEMS_PRICE, $minimalItemsPrice);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getApplyTax()
    {
        return $this->_getData(GiftRuleInterface::APPLY_TAX);
    }

    /**
     * @inheritdoc
     */
    public function setApplyTax($applyTax)
    {
        $this->setData(GiftRuleInterface::APPLY_TAX, $applyTax);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getApplyShipping()
    {
        return $this->_getData(GiftRuleInterface::APPLY_SHIPPING);
    }

    /**
     * @inheritdoc
     */
    public function setApplyShipping($applyShipping)
    {
        $this->setData(GiftRuleInterface::APPLY_SHIPPING, $applyShipping);

        return $this;
    }
}
