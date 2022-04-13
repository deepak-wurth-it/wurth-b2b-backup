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


declare(strict_types=1);

namespace Mirasvit\Search\Api\Data\Index;

use Magento\Framework\Data\Collection;
use Mirasvit\Search\Api\Data\IndexInterface;

interface InstanceInterface
{
    const INDEX_PREFIX = 'mst_search_';

    public function getName(): string;

    public function getIdentifier(): string;

    public function getType(): string;

    public function getIndexName(): string;

    public function getPrimaryKey(): string;

    public function getAttributes(): array;

    public function getAttributeWeights(): array;

    public function setIndex(IndexInterface $index): InstanceInterface;

    public function getIndex(): ?IndexInterface;

    public function reindexAll(): InstanceInterface;

    public function getIndexableDocuments(int $storeId, array $entityIds = [], int $lastEntityId = 0, int $limit = 100): array;

    /**
     * @return Collection
     */
    public function buildSearchCollection(): Collection;
}
