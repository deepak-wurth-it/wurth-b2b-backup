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
 * @package   mirasvit/module-navigation
 * @version   2.0.12
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\LayeredNavigation\Model;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\LayeredNavigation\Api\Data\AttributeConfigInterface;
use Mirasvit\LayeredNavigation\Model\AttributeConfig\OptionConfig;

class AttributeConfig extends AbstractModel implements AttributeConfigInterface
{
    public function getId(): ?int
    {
        return $this->getData(self::ID) ? (int)$this->getData(self::ID) : null;
    }

    public function getAttributeId(): int
    {
        return (int)$this->getData(self::ATTRIBUTE_ID);
    }

    public function setAttributeId(int $value): AttributeConfigInterface
    {
        return $this->setData(self::ATTRIBUTE_ID, $value);
    }

    public function getAttributeCode(): string
    {
        return (string)$this->getData(self::ATTRIBUTE_CODE);
    }

    public function setAttributeCode(string $value): AttributeConfigInterface
    {
        return $this->setData(self::ATTRIBUTE_CODE, $value);
    }

    public function getConfig(): array
    {
        $value = $this->getData(self::CONFIG);

        try {
            return \Zend_Json::decode($value);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function setConfig(array $value): AttributeConfigInterface
    {
        return $this->setData(self::CONFIG, \Zend_Json::encode($value));
    }

    public function getOptionsConfig(): array
    {
        $value = $this->getConfigData(self::OPTIONS_CONFIG, []);

        $options = [];
        foreach ($value as $data) {
            $options[] = new OptionConfig($data);
        }

        return $options;
    }

    public function setOptionsConfig(array $value): AttributeConfigInterface
    {
        $options = [];
        foreach ($value as $item) {
            $options[] = $item->getData();
        }

        return $this->setConfigData(self::OPTIONS_CONFIG, $options);
    }

    public function getCategoryVisibilityMode(): string
    {
        return (string)$this->getConfigData(self::CATEGORY_VISIBILITY_MODE, self::CATEGORY_VISIBILITY_MODE_ALL);
    }

    public function setCategoryVisibilityMode(string $value): AttributeConfigInterface
    {
        return $this->setConfigData(self::CATEGORY_VISIBILITY_MODE, $value);
    }

    public function getCategoryVisibilityIds(): array
    {
        return (array)$this->getConfigData(self::CATEGORY_VISIBILITY_IDS, []);
    }

    public function setCategoryVisibilityIds(array $value): AttributeConfigInterface
    {
        return $this->setConfigData(self::CATEGORY_VISIBILITY_IDS, $value);
    }

    public function getOptionsSortBy(): string
    {
        return (string)$this->getConfigData(self::OPTIONS_SORT_BY, self::OPTION_SORT_BY_POSITION);
    }

    public function setOptionsSortBy(string $value): AttributeConfigInterface
    {
        return $this->setConfigData(self::OPTIONS_SORT_BY, $value);
    }

    public function getDisplayMode(): string
    {
        return (string)$this->getConfigData(self::DISPLAY_MODE, self::DISPLAY_MODE_LABEL);
    }

    public function setDisplayMode(string $value): AttributeConfigInterface
    {
        return $this->setConfigData(self::DISPLAY_MODE, $value);
    }

    public function getValueTemplate(): string
    {
        return (string)$this->getConfigData(self::VALUE_TEMPLATE);
    }

    public function setValueTemplate(string $value): AttributeConfigInterface
    {
        return $this->setConfigData(self::VALUE_TEMPLATE, $value);
    }

    public function isShowSearchBox(): bool
    {
        return (bool)$this->getConfigData(self::IS_SHOW_SEARCH_BOX);
    }

    public function setIsShowSearchBox(bool $value): AttributeConfigInterface
    {
        return $this->setConfigData(self::IS_SHOW_SEARCH_BOX, $value);
    }

    protected function _construct()
    {
        $this->_init(ResourceModel\AttributeConfig::class);
    }

    /**
     * @param string      $key
     * @param null|string $default
     *
     * @return mixed|null
     */
    private function getConfigData(string $key, $default = null)
    {
        $config = $this->getConfig();

        return isset($config[$key]) ? $config[$key] : $default;
    }

    /**
     * @param string       $key
     * @param string|mixed $value
     *
     * @return self
     */
    private function setConfigData(string $key, $value): AttributeConfigInterface
    {
        $config       = $this->getConfig();
        $config[$key] = $value;

        return $this->setConfig($config);
    }
}
