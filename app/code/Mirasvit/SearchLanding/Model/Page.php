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



namespace Mirasvit\SearchLanding\Model;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\SearchLanding\Api\Data\PageInterface;

class Page extends AbstractModel implements PageInterface
{
    public function getId(): int
    {
        return (int)$this->getData(self::ID);
    }

    public function getQueryText(): string
    {
        return (string)$this->getData(self::QUERY_TEXT);
    }

    public function setQueryText(string $value): PageInterface
    {
        return $this->setData(self::QUERY_TEXT, $value);
    }

    public function getUrlKey(): string
    {
        return (string)$this->getData(self::URL_KEY);
    }

    public function setUrlKey(string $value): PageInterface
    {
        return $this->setData(self::URL_KEY, $value);
    }

    public function getTitle(): string
    {
        return (string)$this->getData(self::TITLE);
    }

    public function setTitle(string $value): PageInterface
    {
        return $this->setData(self::TITLE, $value);
    }

    public function getMetaDescription(): string
    {
        return (string)$this->getData(self::META_DESCRIPTION);
    }

    public function setMetaDescription(string $value): PageInterface
    {
        return $this->setData(self::META_DESCRIPTION, $value);
    }

    public function getMetaKeywords(): string
    {
        return (string)$this->getData(self::META_KEYWORDS);
    }

    public function setMetaKeywords(string $value): PageInterface
    {
        return $this->setData(self::META_KEYWORDS, $value);
    }

    public function getLayoutUpdate(): string
    {
        return (string)$this->getData(self::LAYOUT_UPDATE);
    }

    public function setLayoutUpdate(string $value): PageInterface
    {
        return $this->setData(self::LAYOUT_UPDATE, $value);
    }

    public function getStoreIds(): array
    {
        return array_filter(explode(',', $this->getData(self::STORE_IDS)));
    }

    public function setStoreIds(array $value): PageInterface
    {
        return $this->setData(self::STORE_IDS, implode(',', $value));
    }

    public function isActive(): bool
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    public function setIsActive(bool $value): PageInterface
    {
        return $this->setData(self::IS_ACTIVE, $value);
    }

    protected function _construct(): void
    {
        $this->_init(ResourceModel\Page::class);
    }
}
