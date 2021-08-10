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
 * @package   mirasvit/module-seo-filter
 * @version   1.1.5
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\SeoFilter\Api\Data;

interface RewriteInterface
{
    const TABLE_NAME = 'mst_seo_filter_rewrite';

    const ID             = 'rewrite_id';
    const ATTRIBUTE_CODE = 'attribute_code';
    const OPTION         = 'option';
    const REWRITE        = 'rewrite';
    const STORE_ID       = 'store_id';

    public function getId(): ?int;

    public function getAttributeCode(): string;

    public function setAttributeCode(string $value): self;

    public function getOption(): string;

    public function setOption(string $value): self;

    public function getRewrite(): string;

    public function setRewrite(string $value): self;

    public function getStoreId(): int;

    public function setStoreId(int $value): self;
}
