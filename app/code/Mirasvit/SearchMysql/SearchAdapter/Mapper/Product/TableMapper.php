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



namespace Mirasvit\SearchMysql\SearchAdapter\Mapper\Product;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\RequestInterface;

class TableMapper
{
    private $filterStrategy;

    private $aliasResolver;

    private $filtersExtractor;

    public function __construct(
        FilterMapper\FilterContext $filterStrategy,
        Filter\AliasResolver $aliasResolver,
        Filter\FiltersExtractor $filtersExtractor
    ) {
        $this->filterStrategy   = $filterStrategy;
        $this->aliasResolver    = $aliasResolver;
        $this->filtersExtractor = $filtersExtractor;
    }

    public function addTables(Select $select, RequestInterface $request): Select
    {
        $appliedFilters = [];
        $filters        = $this->filtersExtractor->extractFiltersFromQuery($request->getQuery());
        foreach ($filters as $filter) {
            $alias = $this->aliasResolver->getAlias($filter);
            if (!array_key_exists($alias, $appliedFilters)) {
                $isApplied = $this->filterStrategy->apply($filter, $select);
                if ($isApplied) {
                    $appliedFilters[$alias] = true;
                }
            }
        }

        return $select;
    }
}
