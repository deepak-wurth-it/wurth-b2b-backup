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



namespace Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\FilterMapper;

use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\ObjectManager;
use Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\Filter\AliasResolver;
use Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\SelectContainer\SelectContainer;

class FilterMapper
{
    private $aliasResolver;

    private $customAttributeFilter;

    private $filterStrategy;

    private $visibilityFilter;

    private $stockStatusFilter;

    private $customAttributeStockStatusFilter;

    public function __construct(
        AliasResolver $aliasResolver,
        CustomAttributeFilter $customAttributeFilter,
        FilterContext $filterStrategy,
        VisibilityFilter $visibilityFilter,
        StockStatusFilter $stockStatusFilter,
        ?CustomAttributeStockStatusFilter $customAttributeStockStatusFilter = null
    ) {
        $this->aliasResolver                    = $aliasResolver;
        $this->customAttributeFilter            = $customAttributeFilter;
        $this->filterStrategy                   = $filterStrategy;
        $this->visibilityFilter                 = $visibilityFilter;
        $this->stockStatusFilter                = $stockStatusFilter;
        $this->customAttributeStockStatusFilter = $customAttributeStockStatusFilter
            ?? ObjectManager::getInstance()->get(CustomAttributeStockStatusFilter::class);
    }

    public function applyFilters(SelectContainer $selectContainer): SelectContainer
    {
        $select = $selectContainer->getSelect();

        $select = $this->stockStatusFilter->apply(
            $select,
            Stock::STOCK_IN_STOCK,
            StockStatusFilter::FILTER_JUST_ENTITY,
            $selectContainer->isShowOutOfStockEnabled()
        );

        if ($selectContainer->hasCustomAttributesFilters()) {
            $select = $this->customAttributeFilter->apply($select, ...$selectContainer->getCustomAttributesFilters());
            $select = $this->customAttributeStockStatusFilter->apply(
                $select,
                $selectContainer->isShowOutOfStockEnabled() ? null : Stock::STOCK_IN_STOCK,
                ...$selectContainer->getCustomAttributesFilters()
            );
        }

        $appliedFilters = [];

        if ($selectContainer->hasVisibilityFilter()) {
            $filterType = VisibilityFilter::FILTER_BY_WHERE;
            if ($selectContainer->hasCustomAttributesFilters()) {
                $filterType = VisibilityFilter::FILTER_BY_JOIN;
            }

            $select                                                                                  = $this->visibilityFilter->apply($select, $selectContainer->getVisibilityFilter(), $filterType);
            $appliedFilters[$this->aliasResolver->getAlias($selectContainer->getVisibilityFilter())] = true;
        }

        foreach ($selectContainer->getNonCustomAttributesFilters() as $filter) {
            $alias = $this->aliasResolver->getAlias($filter);

            if (!array_key_exists($alias, $appliedFilters)) {
                $isApplied = $this->filterStrategy->apply($filter, $select);
                if ($isApplied) {
                    $appliedFilters[$alias] = true;
                }
            }
        }

        $selectContainer = $selectContainer->updateSelect($select);

        return $selectContainer;
    }
}
