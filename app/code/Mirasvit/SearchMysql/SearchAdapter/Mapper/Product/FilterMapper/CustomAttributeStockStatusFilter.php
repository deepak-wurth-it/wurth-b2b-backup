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

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\FilterInterface;
use Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\Filter\AliasResolver;

/**
 * Add stock status filter for each requested filter
 */
class CustomAttributeStockStatusFilter
{
    private const STOCK_STATUS_TABLE_ALIAS_SUFFIX = '_stock_index';
    private const TARGET_ATTRIBUTE_TYPES
                                                  = [
            'select',
            'multiselect',
        ];

    private $eavConfig;

    private $aliasResolver;

    private $stockStatusQueryBuilder;

    public function __construct(
        EavConfig $eavConfig,
        AliasResolver $aliasResolver,
        StockStatusQueryBuilder $stockStatusQueryBuilder
    ) {
        $this->eavConfig               = $eavConfig;
        $this->aliasResolver           = $aliasResolver;
        $this->stockStatusQueryBuilder = $stockStatusQueryBuilder;
    }

    /**
     * Apply stock status filter to provided filter
     *
     * @param Select $select
     * @param mixed $values
     * @param FilterInterface ...$filters
     * @return Select
     */
    public function apply(Select $select, $values = null, FilterInterface ...$filters): Select
    {
        $select = clone $select;
        foreach ($filters as $filter) {
            if ($this->isApplicable($filter)) {
                $mainTableAlias  = $this->aliasResolver->getAlias($filter);
                $stockTableAlias = $mainTableAlias . self::STOCK_STATUS_TABLE_ALIAS_SUFFIX;
                $select          = $this->stockStatusQueryBuilder->apply(
                    $select,
                    $mainTableAlias,
                    $stockTableAlias,
                    'source_id',
                    $values
                );
            }
        }

        return $select;
    }

    private function isApplicable(FilterInterface $filter): bool
    {
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $filter->getField());

        return $attribute
            && $filter->getType() === FilterInterface::TYPE_TERM
            && in_array($attribute->getFrontendInput(), self::TARGET_ATTRIBUTE_TYPES, true);
    }
}
