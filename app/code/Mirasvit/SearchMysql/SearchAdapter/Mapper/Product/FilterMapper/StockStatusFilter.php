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

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;


class StockStatusFilter
{
    const FILTER_JUST_ENTITY             = 'general_filter';
    const FILTER_ENTITY_AND_SUB_PRODUCTS = 'filter_with_sub_products';

    private $resourceConnection;

    private $conditionManager;

    private $stockConfiguration;

    private $stockRegistry;

    private $stockStatusQueryBuilder;

    public function __construct(
        ResourceConnection $resourceConnection,
        ConditionManager $conditionManager,
        StockConfigurationInterface $stockConfiguration,
        StockRegistryInterface $stockRegistry,
        ?StockStatusQueryBuilder $stockStatusQueryBuilder = null
    ) {
        $this->resourceConnection      = $resourceConnection;
        $this->conditionManager        = $conditionManager;
        $this->stockConfiguration      = $stockConfiguration;
        $this->stockRegistry           = $stockRegistry;
        $this->stockStatusQueryBuilder = $stockStatusQueryBuilder
            ?? ObjectManager::getInstance()->get(StockStatusQueryBuilder::class);
    }

    public function apply(Select $select, int $stockValues, string $type, bool $showOutOfStockFlag): Select
    {
        if ($type !== self::FILTER_JUST_ENTITY && $type !== self::FILTER_ENTITY_AND_SUB_PRODUCTS) {
            throw new \InvalidArgumentException(sprintf('Invalid filter type: %s', $type));
        }

        $select         = clone $select;
        $mainTableAlias = $this->extractTableAliasFromSelect($select);

        $select = $this->stockStatusQueryBuilder->apply(
            $select,
            $mainTableAlias,
            'stock_index',
            'entity_id',
            $showOutOfStockFlag ? null : [$stockValues]
        );

        if ($type === self::FILTER_ENTITY_AND_SUB_PRODUCTS) {
            $select = $this->stockStatusQueryBuilder->apply(
                $select,
                $mainTableAlias,
                'sub_products_stock_index',
                'source_id',
                $showOutOfStockFlag ? null : [$stockValues]
            );
        }

        return $select;
    }

    private function extractTableAliasFromSelect(Select $select): ?string
    {
        $fromArr = array_filter(
            $select->getPart(Select::FROM),
            function ($fromPart) {
                return $fromPart['joinType'] === Select::FROM;
            }
        );

        return $fromArr ? array_keys($fromArr)[0] : null;
    }
}
