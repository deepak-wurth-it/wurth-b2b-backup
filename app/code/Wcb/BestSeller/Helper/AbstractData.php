<?php

namespace Wcb\BestSeller\Helper;

use Exception;
use Magento\Backend\App\Config;
use Magento\Backend\App\ConfigInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\State;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Zend\Serializer\Adapter\PhpSerialize;


/**
 * Class AbstractData
 * @package Wcb\BestSeller\Helper
 */
class AbstractData extends AbstractHelper
{
    const CONFIG_MODULE_PATH = 'Wcb Bestseller Slider';
    /**
     * @var ConfigInterface
     */
    protected $_backendConfig;
    /**
     * @type array
     */
    protected $_data = [];

    /**
     * @type StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Config
     */
    protected $backendConfig;

    /**
     * @var array
     */
    protected $isArea = [];
    protected $_urlInterface;
    protected $_productMetadataInterface;
    protected $_state;
    protected $_phpSerialize;
    protected $_jsonHelper;

    /**
     * AbstractData constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ConfigInterface $backendConfig
     * @param UrlInterface $urlInterface
     * @param ProductMetadataInterface $productMetadataInterface
     * @param State $state
     * @param PhpSerialize $phpSerialize
     * @param JsonHelper $jsonHelper
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ConfigInterface $backendConfig,
        UrlInterface $urlInterface,
        ProductMetadataInterface $productMetadataInterface,
        State $state,
        PhpSerialize $phpSerialize,
        JsonHelper $jsonHelper
    )
    {
        $this->storeManager = $storeManager;
        $this->_backendConfig = $backendConfig;
        $this->_urlInterface = $urlInterface;
        $this->_productMetadataInterface = $productMetadataInterface;
        $this->_state = $state;
        $this->_phpSerialize = $phpSerialize;
        $this->_jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return $this->getConfigGeneral('enabled', $storeId);
    }

    /**
     * @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getConfigGeneral($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(static::CONFIG_MODULE_PATH . '/general' . $code, $storeId);
    }

    /**
     * @param $field
     * @param null $scopeValue
     * @param string $scopeType
     *
     * @return array|mixed
     */
    public function getConfigValue($field, $scopeValue = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        if ($scopeValue === null && !$this->isArea()) {
            /** @var Config $backendConfig */
            if (!$this->backendConfig) {
                $this->backendConfig = $this->_backendConfig;
            }

            return $this->backendConfig->getValue($field);
        }

        return $this->scopeConfig->getValue($field, $scopeType, $scopeValue);
    }

    /**
     * @param string $area
     *
     * @return mixed
     */
    public function isArea($area = Area::AREA_FRONTEND)
    {
        if (!isset($this->isArea[$area])) {
            /** @var State $state */
            $state = $this->_state;
            try {
                $this->isArea[$area] = ($state->getAreaCode() == $area);
            } catch (Exception $e) {
                $this->isArea[$area] = false;
            }
        }

        return $this->isArea[$area];
    }

    /**
     * @param string $field
     * @param null $storeId
     *
     * @return mixed
     */
    public function getModuleConfig($field = '', $storeId = null)
    {
        $field = ($field !== '') ? '/' . $field : '';

        return $this->getConfigValue(static::CONFIG_MODULE_PATH . $field, $storeId);
    }

    /**
     * @param $name
     *
     * @return null
     */
    public function getData($name)
    {
        if (array_key_exists($name, $this->_data)) {
            return $this->_data[$name];
        }

        return null;
    }

    /**
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function setData($name, $value)
    {
        $this->_data[$name] = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCurrentUrl()
    {
        return $this->_urlInterface->getCurrentUrl();
    }

    /**
     * @param $data
     *
     * @return string
     */
    public function serialize($data)
    {
        if ($this->versionCompare('2.2.0')) {
            return self::jsonEncode($data);
        }

        return $this->getSerializeClass()->serialize($data);
    }

    /**
     * @param $ver
     * @param string $operator
     *
     * @return mixed
     */
    public function versionCompare($ver, $operator = '>=')
    {
        $version = $this->_productMetadataInterface->getVersion(); //will return the magento version
        return version_compare($version, $ver, $operator);
    }

    /**
     * Encode the mixed $valueToEncode into the JSON format
     *
     * @param mixed $valueToEncode
     *
     * @return string
     */
    public static function jsonEncode($valueToEncode)
    {
        try {
            $encodeValue = self::getJsonHelper()->jsonEncode($valueToEncode);
        } catch (Exception $e) {
            $encodeValue = '{}';
        }

        return $encodeValue;
    }

    /**
     * @return JsonHelper|mixed
     */
    public static function getJsonHelper()
    {
        return $this->_jsonHelper;
    }

    /**
     * @return mixed
     */
    protected function getSerializeClass()
    {
        return $this->_phpSerialize;
    }

    /**
     * @param $string
     *
     * @return mixed
     */
    public function unserialize($string)
    {
        if ($this->versionCompare('2.2.0')) {
            return self::jsonDecode($string);
        }

        return $this->getSerializeClass()->unserialize($string);
    }

    /**
     * Decodes the given $encodedValue string which is
     * encoded in the JSON format
     *
     * @param string $encodedValue
     *
     * @return mixed
     */
    public static function jsonDecode($encodedValue)
    {
        try {
            $decodeValue = self::getJsonHelper()->jsonDecode($encodedValue);
        } catch (Exception $e) {
            $decodeValue = [];
        }

        return $decodeValue;
    }

    /**
     * Is Admin Store
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->isArea(Area::AREA_ADMINHTML);
    }
}
