<?php

namespace Amasty\Promo\Model;

/**
 * Config Provider
 */
class Config
{
    const PROMO_SECTION = 'ampromo/';

    const GROUP_PROMO_MESSAGES = 'messages/';

    const GENERAL_GROUP = 'general/';

    const GIFT_IMAGES_GROUP = 'gift_images/';

    const PREFIX_FIELD = 'prefix';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $config;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $group
     * @param string|null $path
     * @param mixed|null $store
     * @return string
     */
    public function getScopeValue($group, $path = null, $store = null)
    {
        return $this->config->getValue(
            self::PROMO_SECTION . $group . $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param $group
     * @param $path
     *
     * @return bool
     */
    private function isSetFlag($group, $path)
    {
        return $this->config->isSetFlag(
            self::PROMO_SECTION . $group . $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getSelectionMethod()
    {
        return $this->getScopeValue(self::GROUP_PROMO_MESSAGES, 'gift_selection_method');
    }

    /**
     * @param mixed|null $store
     * @return int
     */
    public function getGiftRepresentationMode($store = null): int
    {
        return (int)$this->getScopeValue(self::GROUP_PROMO_MESSAGES, 'gift_representation_mode', $store);
    }

    /**
     * @return mixed
     */
    public function getGiftsCounter()
    {
        return $this->getScopeValue(self::GROUP_PROMO_MESSAGES, 'display_remaining_gifts_counter');
    }

    /**
     * @return mixed
     */
    public function getAddMessage()
    {
        return $this->getScopeValue(self::GROUP_PROMO_MESSAGES, 'add_message');
    }

    /**
     * @return bool
     */
    public function isAutoOpenPopup()
    {
        return $this->isSetFlag(self::GROUP_PROMO_MESSAGES, 'auto_open_popup');
    }

    /**
     * @return mixed
     */
    public function getAutoAddType()
    {
        return $this->getScopeValue(self::GENERAL_GROUP, 'auto_add');
    }

    /**
     * @return mixed
     */
    public function getPopupName()
    {
        return $this->getScopeValue(self::GROUP_PROMO_MESSAGES, 'popup_title');
    }

    /**
     * @return mixed
     */
    public function getAddButtonName()
    {
        return $this->getScopeValue(self::GROUP_PROMO_MESSAGES, 'add_button_title');
    }

    /**
     * @return int
     */
    public function isDiscountIncluded()
    {
        return (bool) $this->getScopeValue(self::GENERAL_GROUP, 'discount_include');
    }

    /**
     * @return bool
     */
    public function isTaxIncluded(): bool
    {
        return (bool)$this->getScopeValue(self::GENERAL_GROUP, 'tax_include');
    }

    /**
     * @return string
     */
    public function getAttrForHeader()
    {
        return $this->getScopeValue(self::GIFT_IMAGES_GROUP, 'attribute_header');
    }

    /**
     * @return string
     */
    public function getAttrForDescription()
    {
        return $this->getScopeValue(self::GIFT_IMAGES_GROUP, 'attribute_description');
    }

    /**
     * @return float
     */
    public function getImageWidth()
    {
        return $this->getScopeValue(self::GIFT_IMAGES_GROUP, 'gift_image_width');
    }

    /**
     * @return float
     */
    public function getImageHeight()
    {
        return $this->getScopeValue(self::GIFT_IMAGES_GROUP, 'gift_image_height');
    }

    /**
     * @return string
     */
    public function getProductPrefix()
    {
        return $this->getScopeValue(self::GROUP_PROMO_MESSAGES, self::PREFIX_FIELD);
    }
}
