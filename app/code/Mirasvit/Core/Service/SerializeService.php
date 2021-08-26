<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-core
 * @version   1.2.122
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Core\Service;

use Zend\Serializer\Serializer as ZendSerializer;

class SerializeService
{
    /**
     * @var ZendSerializer | \Magento\Framework\Serialize\Serializer\Serialize
     */
    private static $serializer;

    /**
     * @var ZendSerializer | \Magento\Framework\Serialize\Serializer\Json
     */
    private static $jsoner;

    private static $isOldVersion = null;

    public static function init()
    {
        if (self::isOldVersion()) {
            self::$serializer = ZendSerializer::factory('PhpSerialize');
            self::$jsoner     = ZendSerializer::factory('Json');
        } else {
            /** @var \Magento\Framework\Serialize\Serializer\Serialize $serializer */
            self::$serializer = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Serialize\Serializer\Serialize::class
            );
            self::$jsoner     = CompatibilityService::getObjectManager()
                ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        }
    }

    public static function isOldVersion()
    {
        if (self::$isOldVersion === null) {
            self::$isOldVersion = CompatibilityService::is21() || CompatibilityService::is20();
        }

        return self::$isOldVersion;
    }

    /**
     * @param array|string $data
     *
     * @return string|null
     */
    public static function encode($data)
    {
        self::init();

        $serialized = true;

        try {
            $result = self::$jsoner->serialize($data);
        } catch (\Exception $e) {
            $serialized = false;
        }

        if (!$serialized) {
            try {
                $result = self::$serializer->serialize($data);
            } catch (\Exception $e) {
                $result = null;
            }
        }

        return $result;
    }

    /**
     * @param string $string
     *
     * @return array
     * @throws \Zend_Json_Exception
     */
    public static function decode($string)
    {
        if (!is_string($string)) {
            return null;
        }

        self::init();

        $unserialized = true;

        try {
            new \ReflectionClass('Zend\Json\Json');
        } catch (\Exception $e) {}

        // we use this because json_decode does not work correct for php5
        if (class_exists('Zend\Json\Json', false)) {
            $useDecoder                                = \Zend\Json\Json::$useBuiltinEncoderDecoder;
            \Zend\Json\Json::$useBuiltinEncoderDecoder = true;
        }

        try {
            $result = self::$jsoner->unserialize($string);
        } catch (\Exception $e) {
            $unserialized = false;
        }

        if (!$unserialized) {
            try {
                $result = self::$serializer->unserialize($string);
            } catch (\Exception $e) {
                $result = null;
            }
        }

        if (class_exists('Zend\Json\Json', false)) {
            \Zend\Json\Json::$useBuiltinEncoderDecoder = $useDecoder;
        }

        return $result;
    }

    /**
     * @param string|array $data
     *
     * @return string|null
     */
    public static function encodeWithNewMagento($data)
    {
        self::$isOldVersion = false;

        return self::encode($data);
    }

    /**
     * @param string|array $data
     *
     * @return string|null
     */
    public static function encodeWithOldMagento($data)
    {
        self::$isOldVersion = true;

        return self::encode($data);
    }

    /**
     * @param string|array $data
     *
     * @return array
     * @throws \Zend_Json_Exception
     */
    public static function decodeWithNewMagento($data)
    {
        self::$isOldVersion = false;

        return self::decode($data);
    }

    /**
     * @param string|array $data
     *
     * @return array
     * @throws \Zend_Json_Exception
     */
    public static function decodeWithOldMagento($data)
    {
        self::$isOldVersion = true;

        return self::decode($data);
    }
}
