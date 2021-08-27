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

namespace Mirasvit\QuickNavigation\Api\Data;

interface SequenceInterface
{
    const TABLE_NAME = 'mst_quick_navigation_sequence';

    const ID          = 'sequence_id';
    const STORE_ID    = 'store_id';
    const CATEGORY_ID = 'category_id';
    const SEQUENCE    = 'sequence';
    const LENGTH      = 'length';
    const POPULARITY  = 'popularity';

    public function getId(): ?int;

    public function getStoreId(): int;

    public function setStoreId(int $value): self;

    public function getCategoryId(): int;

    public function setCategoryId(int $value): self;

    public function getSequence(): string;

    public function setSequence(string $value): self;

    public function getLength(): int;

    public function setLength(int $value): self;

    public function getPopularity(): int;

    public function setPopularity(int $value): self;
}
