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
 * @package   mirasvit/module-search-ultimate
 * @version   2.0.56
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Model;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\Core\Service\SerializeService;
use Mirasvit\Search\Api\Data\IndexInterface;

class Index extends AbstractModel implements IndexInterface
{
    public function getId(): int
    {
        return (int)parent::getData(self::ID);
    }

    public function getTitle(): string
    {
        return (string)parent::getData(self::TITLE);
    }

    public function setTitle(string $input): IndexInterface
    {
        return parent::setData(self::TITLE, $input);
    }

    public function getIdentifier(): ?string
    {
        return parent::getData(self::IDENTIFIER);
    }

    public function setIdentifier(string $input): IndexInterface
    {
        return parent::setData(self::IDENTIFIER, $input);
    }

    public function getPosition(): int
    {
        return (int)parent::getData(self::POSITION);
    }

    public function setPosition(int $value): IndexInterface
    {
        return parent::setData(self::POSITION, $value);
    }

    public function getAttributes(): array
    {
        if (empty(parent::getData(self::ATTRIBUTES_SERIALIZED))) {
            return [];
        }

        try {
            $data = (array)\Zend_Json::decode(parent::getData(self::ATTRIBUTES_SERIALIZED));
        } catch (\Exception $e) {
            $data = [];
        }

        return $data;
    }

    public function setAttributes(array $input): IndexInterface
    {
        return parent::setData(self::ATTRIBUTES_SERIALIZED, \Zend_Json::encode($input));
    }

    public function setProperties(array $input): IndexInterface
    {
        return parent::setData(self::PROPERTIES_SERIALIZED, \Zend_Json::encode($input));
    }

    public function getStatus(): int
    {
        return (int)parent::getData(self::STATUS);
    }

    public function setStatus(int $value): IndexInterface
    {
        return parent::setData(self::STATUS, $value);
    }

    public function getIsActive(): bool
    {
        return (bool)parent::getData(self::IS_ACTIVE);
    }

    public function setIsActive(bool $value): IndexInterface
    {
        return parent::setData(self::IS_ACTIVE, $value);
    }

    public function getProperty(string $key): string
    {
        $props = $this->getProperties();
        if (isset($props[$key]) && is_array($props[$key])) {
            $props[$key] = \Zend_Json::encode($props[$key]);
        }

        return $props[$key] ?? '';
    }

    public function getProperties(): array
    {
        if (empty(parent::getData(self::PROPERTIES_SERIALIZED))) {
            return [];
        }

        return (array)SerializeService::decode(parent::getData(self::PROPERTIES_SERIALIZED));
    }

    protected function _construct(): void
    {
        $this->_init(ResourceModel\Index::class);

        parent::_construct();
    }
}
