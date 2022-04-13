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



namespace Mirasvit\SearchReport\Model;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\SearchReport\Api\Data\LogInterface;

class Log extends AbstractModel implements LogInterface
{
    public function getId(): int
    {
        return (int)$this->getData(LogInterface::ID);
    }

    public function getQuery(): string
    {
        return $this->getData(LogInterface::QUERY);
    }

    public function setQuery(string $value): LogInterface
    {
        return $this->setData(LogInterface::QUERY, $value);
    }

    public function getFallbackQuery(): string
    {
        return (string)$this->getData(LogInterface::FALLBACK_QUERY);
    }

    public function setFallbackQuery(string $value): LogInterface
    {
        return $this->setData(LogInterface::FALLBACK_QUERY, $value);
    }

    public function getMisspellQuery(): string
    {
        return (string)$this->getData(LogInterface::MISSPELL_QUERY);
    }

    public function setMisspellQuery(string $value): LogInterface
    {
        return $this->setData(LogInterface::MISSPELL_QUERY, $value);
    }

    public function getResults(): int
    {
        return (int)$this->getData(LogInterface::RESULTS);
    }

    public function setResults(int $value): LogInterface
    {
        return $this->setData(LogInterface::RESULTS, $value);
    }

    public function getIp(): string
    {
        return $this->getData(LogInterface::IP);
    }

    public function setIp(string $value): LogInterface
    {
        return $this->setData(LogInterface::IP, $value);
    }

    public function getSession(): string
    {
        return $this->getData(LogInterface::SESSION);
    }

    public function setSession(string $value): LogInterface
    {
        return $this->setData(LogInterface::SESSION, $value);
    }

    public function getCustomerId(): int
    {
        return (int)$this->getData(LogInterface::CUSTOMER_ID);
    }

    public function setCustomerId(int $value): LogInterface
    {
        return $this->setData(LogInterface::CUSTOMER_ID, $value);
    }

    public function getCountry(): string
    {
        return $this->getData(LogInterface::COUNTRY);
    }

    public function setCountry(string $value): LogInterface
    {
        return $this->setData(LogInterface::COUNTRY, $value);
    }

    public function getOrderItemId(): int
    {
        return (int)$this->getData(LogInterface::ORDER_ITEM_ID);
    }

    public function setOrderItemId(int $value): LogInterface
    {
        return $this->setData(LogInterface::ORDER_ITEM_ID, $value);
    }

    public function getClicks(): int
    {
        return (int)$this->getData(LogInterface::CLICKS);
    }

    public function setClicks(int $value): LogInterface
    {
        return $this->setData(LogInterface::CLICKS, $value);
    }

    public function getSource(): string
    {
        return $this->getData(LogInterface::SOURCE);
    }

    public function setSource(string $value): LogInterface
    {
        return $this->setData(LogInterface::SOURCE, $value);
    }

    public function getCreatedAt(): string
    {
        return $this->getData(LogInterface::CREATED_AT);
    }

    public function setCreatedAt(string $value): LogInterface
    {
        return $this->setData(LogInterface::CREATED_AT, $value);
    }

    protected function _construct()
    {
        $this->_init(\Mirasvit\SearchReport\Model\ResourceModel\Log::class);
    }
}
