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



namespace Mirasvit\SearchLanding\Api\Data;

interface PageInterface
{
    const TABLE_NAME = 'mst_search_landing_page';

    const ID               = 'page_id';
    const QUERY_TEXT       = 'query_text';
    const URL_KEY          = 'url_key';
    const TITLE            = 'title';
    const META_KEYWORDS    = 'meta_keywords';
    const META_DESCRIPTION = 'meta_description';
    const LAYOUT_UPDATE    = 'layout_update';
    const STORE_IDS        = 'store_ids';
    const IS_ACTIVE        = 'is_active';

    public function getId(): int;

    public function getQueryText(): string;

    public function setQueryText(string $value): PageInterface;

    public function getUrlKey(): string;

    public function setUrlKey(string $value): PageInterface;

    public function getTitle(): string;

    public function setTitle(string $value): PageInterface;

    public function getMetaKeywords(): string;

    public function setMetaKeywords(string $value): PageInterface;

    public function getMetaDescription(): string;

    public function setMetaDescription(string $value): PageInterface;

    public function getLayoutUpdate(): string;

    public function setLayoutUpdate(string $value): PageInterface;

    public function getStoreIds(): array;

    public function setStoreIds(array $value): PageInterface;

    public function isActive(): bool;

    public function setIsActive(bool $value): PageInterface;
}
