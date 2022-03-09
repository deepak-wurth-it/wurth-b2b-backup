<?php
/**
 * Class Data
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_SocialShare
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\SocialShare\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Sparsh\SocialShare\Model\System\ColorSchema;

/**
 * Class Data
 *
 * @category Sparsh
 * @package  Sparsh_SocialShare
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const MODULE_CODE = 'sparshsocialshare';
    const CONFIG_WIDGET  = 'float/';
    const CONFIG_INLINE_WIDGET = 'inline/';
    const CONFIG_MORE = 'add_more_share/';

    /**
     * Area array
     *
     * @var array
     */
    public $isArea = [];

    /**
     * Storemanager
     *
     * @type StoreManagerInterface
     */
    public $storeManager;

    /**
     * Objectmanager
     *
     * @type ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Backendconfig
     *
     * @var Config
     */
    public $backendConfig;
    
    /**
     * Data constructor.
     *
     * @param Context                $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface  $storeManager
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager
    ) {
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;

        parent::__construct($context);
    }

    /**
     * Is social share enabled
     *
     * @param null $storeId
     *
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return $this->getGeneralConfig('enabled', $storeId);
    }

    /**
     * Return General config value of social share
     *
     * @param string $code
     * @param null   $storeId
     *
     * @return mixed
     */
    private function getGeneralConfig($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(static::MODULE_CODE . '/general' . $code, $storeId);
    }

    /**
     * Return config
     *
     * @param string $field
     * @param null   $storeId
     *
     * @return mixed
     */
    private function getConfig($field = '', $storeId = null)
    {
        $field = ($field !== '') ? '/' . $field : '';

        return $this->getConfigValue(static::MODULE_CODE . $field, $storeId);
    }

    /**
     * Get config
     *
     * @param string $field
     * @param null   $scopeValue
     * @param string $scopeType
     *
     * @return array|mixed
     */
    private function getConfigValue($field, $scopeValue = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        if (!$this->isArea() && is_null($scopeValue)) {
            if (!$this->backendConfig) {
                $this->backendConfig = $this->objectManager->get(\Magento\Backend\App\ConfigInterface::class);
            }

            return $this->backendConfig->getValue($field);
        }

        return $this->scopeConfig->getValue($field, $scopeType, $scopeValue);
    }

    /**
     * Check area
     *
     * @param string $area
     *
     * @return mixed
     */
    private function isArea($area = Area::AREA_FRONTEND)
    {
        if (!isset($this->isArea[$area])) {
            $state = $this->objectManager->get(\Magento\Framework\App\State::class);

            try {
                $this->isArea[$area] = ($state->getAreaCode() == $area);
            } catch (\Exception $e) {
                $this->isArea[$area] = false;
            }
        }

        return $this->isArea[$area];
    }

    /**
     * Check if icon's color schema is set to custom or default
     *
     * @return bool
     */
    public function isCustomColorIcon($storeId = null)
    {
        $colorSchema = $this->getGeneralConfig('color_schema', $storeId);
        if ($colorSchema == ColorSchema::CUSTOM) {
            return true;
        }
        return false;
    }

    /**
     * Get Icon color
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getIconColor($storeId = null)
    {
        return $this->getGeneralConfig('custom_icon_color', $storeId);
    }

    /**
     * Get button color
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getButtonColor($storeId = null)
    {
        return $this->getGeneralConfig('custom_button_color', $storeId);
    }

    /**
     * Get background color of buttons
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getBackgroundColor($storeId = null)
    {
        return $this->getGeneralConfig('custom_background_color', $storeId);
    }

    /**
     * Get border radius
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getBorderRadius($storeId = null)
    {
        return $this->getGeneralConfig('border_radius', $storeId);
    }

    /**
     * Get share counter is enabled or not
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function isShareCounter($storeId = null)
    {
        return $this->getGeneralConfig('share_counter', $storeId);
    }

    /**
     * Get thank you popup enables or not
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function isThankYouPopup($storeId = null)
    {
        return $this->getGeneralConfig('thank_you', $storeId);
    }

    /**
     * Return Is specific service is enable ot not
     *
     * @param string $serviceCode
     * @param null   $storeId
     *
     * @return array|mixed
     */
    public function isServiceEnable($serviceCode, $storeId = null)
    {
        return $this->getGeneralConfig($serviceCode, $storeId);
    }

    /**
     * Return Is show more option
     *
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function isAddMoreShare($storeId = null)
    {
        return $this->getGeneralConfig(self::CONFIG_MORE . 'enabled', $storeId);
    }

    /**
     * Get Display menu type
     *
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getDisplayMenuType($storeId = null)
    {
        return $this->getGeneralConfig(self::CONFIG_MORE . 'display_menu', $storeId);
    }

    /**
     * Get no. of services display on show more click or hover
     *
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getNumberOfServices($storeId = null)
    {
        return $this->getGeneralConfig(self::CONFIG_MORE . 'number_service', $storeId);
    }

    /**
     * Return if shoe full menu on click of show more or not
     *
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function isFullMenuOnClick($storeId = null)
    {
        return $this->getGeneralConfig(self::CONFIG_MORE . 'full_menu', $storeId);
    }

    /**
     * Return disables services
     *
     * @param string $serviceCode
     * @param null   $storeId
     *
     * @return null |null
     */
    public function getDisableService($serviceCode, $storeId = null)
    {
        if (!$this->isServiceEnable($serviceCode, $storeId)) {
            return $serviceCode;
        }

        return null;
    }

    /**
     * Return pages on which share widget apply
     *
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getFloatApplyPages($storeId = null)
    {
        return $this->getConfig(self::CONFIG_WIDGET . 'apply_for', $storeId);
    }

    /**
     * Return selected pages on which share widget apply
     *
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getFloatPages($storeId = null)
    {
        return $this->getConfig(self::CONFIG_WIDGET . 'select_page', $storeId);
    }

    /**
     * Return position of share widget
     *
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getPosition($storeId = null)
    {
        return $this->getConfig(self::CONFIG_WIDGET . 'position', $storeId);
    }

    /**
     * Get top margin
     *
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getMarginTop($storeId = null)
    {
        return $this->getConfig(self::CONFIG_WIDGET . 'margin_top', $storeId);
    }

    /**
     * Get bottom margin
     *
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getMarginBottom($storeId = null)
    {
        return $this->getConfig(self::CONFIG_WIDGET . 'margin_bottom', $storeId);
    }

    /**
     * Return button size
     *
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getButtonSize($storeId = null)
    {
        return $this->getConfig(self::CONFIG_WIDGET . 'button_size', $storeId);
    }

    /**
     * Return pages on which inline widget apply
     *
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getInlineApplyPages($storeId = null)
    {
        return $this->getConfig(self::CONFIG_INLINE_WIDGET . 'apply_for', $storeId);
    }

    /**
     * Return cms pages on which share widget applies
     *
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getCmsPages($storeId = null)
    {
        return $this->getConfig(self::CONFIG_WIDGET . 'cms_page', $storeId);
    }

    /**
     * Return widget style
     *
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getStyle($storeId = null)
    {
        return $this->getConfig(self::CONFIG_WIDGET . 'style', $storeId);
    }

    /**
     * Return inline position of inline widget
     *
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getInlinePosition($storeId = null)
    {
        return $this->getConfig(self::CONFIG_INLINE_WIDGET . 'position', $storeId);
    }

    /**
     * Return is show widget under add-to-cart or not
     *
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getShowUnderCart($storeId = null)
    {
        return $this->getConfig(self::CONFIG_INLINE_WIDGET . 'show_under_cart', $storeId);
    }

    /**
     * Return inline widget button size
     *
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getInlineButtonSize($storeId = null)
    {
        return $this->getConfig(self::CONFIG_INLINE_WIDGET . 'button_size', $storeId);
    }
}
