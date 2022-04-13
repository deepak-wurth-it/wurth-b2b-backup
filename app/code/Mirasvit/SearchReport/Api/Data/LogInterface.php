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



namespace Mirasvit\SearchReport\Api\Data;

interface LogInterface
{
    const TABLE_NAME = 'mst_search_report_log';

    const ID             = 'log_id';
    const QUERY          = 'query';
    const FALLBACK_QUERY = 'fallback_query';
    const MISSPELL_QUERY = 'misspell_query';
    const RESULTS        = 'results';
    const IP             = 'ip';
    const SESSION        = 'session';
    const CUSTOMER_ID    = 'customer_id';
    const COUNTRY        = 'country';
    const ORDER_ITEM_ID  = 'order_item_id';
    const CLICKS         = 'clicks';
    const SOURCE         = 'source';
    const CREATED_AT     = 'created_at';

    public function getId(): int;

    public function getQuery(): string;

    public function setQuery(string $value): LogInterface;

    public function getFallbackQuery(): string;

    public function setFallbackQuery(string $value): LogInterface;

    public function getMisspellQuery(): string;

    public function setMisspellQuery(string $value): LogInterface;

    public function getResults(): int;

    public function setResults(int $value): LogInterface;

    public function getIp(): string;

    public function setIp(string $value): LogInterface;

    public function getSession(): string;

    public function setSession(string $value): LogInterface;

    public function getCustomerId(): int;

    public function setCustomerId(int $value): LogInterface;

    public function getCountry(): string;

    public function setCountry(string $value): LogInterface;

    public function getOrderItemId(): int;

    public function setOrderItemId(int $value): LogInterface;

    public function getClicks(): int;

    public function setClicks(int $value): LogInterface;

    public function getSource(): string;

    public function setSource(string $value): LogInterface;

    public function getCreatedAt(): string;

    public function setCreatedAt(string $value): LogInterface;
}
