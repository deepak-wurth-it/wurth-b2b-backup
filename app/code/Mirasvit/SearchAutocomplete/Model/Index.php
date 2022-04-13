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



namespace Mirasvit\SearchAutocomplete\Model;

use Magento\Framework\DataObject;
use Mirasvit\Search\Api\Data\IndexInterface;

class Index extends DataObject
{
    const IDENTIFIER = IndexInterface::IDENTIFIER;
    const TITLE      = IndexInterface::TITLE;
    const IS_ACTIVE  = IndexInterface::IS_ACTIVE;
    const POSITION   = IndexInterface::POSITION;
    const LIMIT      = 'limit';

    public function getIdentifier(): string
    {
        return $this->getData(self::IDENTIFIER);
    }

    public function setIdentifier(string $value): Index
    {
        return $this->setData(self::IDENTIFIER, $value);
    }

    public function getTitle(): string
    {
        return $this->getData(self::TITLE);
    }

    public function setTitle(string $value): Index
    {
        return $this->setData(self::TITLE, $value);
    }

    public function isActive(): bool
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    public function setIsActive(bool $value): Index
    {
        return $this->setData(self::IS_ACTIVE, $value);
    }

    public function getPosition(): int
    {
        return (int)$this->getData(self::POSITION);
    }

    public function setPosition(int $value): Index
    {
        return $this->setData(self::POSITION, $value);
    }

    public function getLimit(): int
    {
        return (int)$this->getData(self::LIMIT);
    }

    public function setLimit(int $value): Index
    {
        return $this->setData(self::LIMIT, $value);
    }
}
