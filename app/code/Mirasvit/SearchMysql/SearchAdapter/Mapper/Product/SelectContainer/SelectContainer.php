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



namespace Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\SelectContainer;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\FilterInterface;

class SelectContainer
{
    private $nonCustomAttributesFilters;

    private $customAttributesFilters;

    private $visibilityFilter;

    private $isFullTextSearchRequired;

    private $isShowOutOfStockEnabled;

    private $select;

    private $usedIndex;

    private $dimensions;

    public function __construct(
        Select $select,
        array $nonCustomAttributesFilters,
        array $customAttributesFilters,
        array $dimensions,
        bool $isFullTextSearchRequired,
        bool $isShowOutOfStockEnabled,
        string $usedIndex,
        FilterInterface $visibilityFilter = null
    ) {
        $this->nonCustomAttributesFilters = $nonCustomAttributesFilters;
        $this->customAttributesFilters    = $customAttributesFilters;
        $this->visibilityFilter           = $visibilityFilter;
        $this->isFullTextSearchRequired   = $isFullTextSearchRequired;
        $this->isShowOutOfStockEnabled    = $isShowOutOfStockEnabled;
        $this->select                     = $select;
        $this->usedIndex                  = $usedIndex;
        $this->dimensions                 = $dimensions;
    }

    public function getNonCustomAttributesFilters(): array
    {
        return $this->nonCustomAttributesFilters;
    }

    public function getCustomAttributesFilters(): array
    {
        return $this->customAttributesFilters;
    }

    public function hasCustomAttributesFilters(): bool
    {
        return count($this->customAttributesFilters) > 0;
    }

    public function hasVisibilityFilter(): bool
    {
        return $this->visibilityFilter !== null;
    }

    public function getVisibilityFilter(): ?FilterInterface
    {
        return $this->visibilityFilter === null ? null : clone $this->visibilityFilter;
    }

    public function isFullTextSearchRequired(): bool
    {
        return $this->isFullTextSearchRequired;
    }

    public function isShowOutOfStockEnabled(): bool
    {
        return $this->isShowOutOfStockEnabled;
    }

    public function getUsedIndex(): string
    {
        return $this->usedIndex;
    }

    public function getDimensions(): array
    {
        return $this->dimensions;
    }

    public function getSelect(): Select
    {
        return clone $this->select;
    }

    public function updateSelect(Select $select): SelectContainer
    {
        $data = [
            clone $select,
            $this->nonCustomAttributesFilters,
            $this->customAttributesFilters,
            $this->dimensions,
            $this->isFullTextSearchRequired,
            $this->isShowOutOfStockEnabled,
            $this->usedIndex,

        ];

        if ($this->visibilityFilter !== null) {
            $data[] = clone $this->visibilityFilter;
        }

        return new self(...$data);
    }
}
