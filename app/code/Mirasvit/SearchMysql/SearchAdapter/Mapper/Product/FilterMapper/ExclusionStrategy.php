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

use Magento\Catalog\Model\Indexer\Category\Product\AbstractAction;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;
use Magento\Framework\Search\Request\IndexScopeResolverInterface as TableResolver;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\Filter\AliasResolver;

class ExclusionStrategy
{
    private $resourceConnection;

    private $aliasResolver;

    private $storeManager;

    private $validFields = ['price', 'category_ids'];

    private $tableResolver;

    private $priceTableResolver;

    private $dimensionFactory;

    private $httpContext;

    public function __construct(
        ResourceConnection $resourceConnection,
        StoreManagerInterface $storeManager,
        AliasResolver $aliasResolver,
        TableResolver $tableResolver = null,
        DimensionFactory $dimensionFactory = null,
        IndexScopeResolverInterface $priceTableResolver = null,
        Context $httpContext = null
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->storeManager       = $storeManager;
        $this->aliasResolver      = $aliasResolver;
        $this->tableResolver      = $tableResolver ? : ObjectManager::getInstance()->get(TableResolver::class);
        $this->dimensionFactory   = $dimensionFactory ? : ObjectManager::getInstance()->get(DimensionFactory::class);
        $this->priceTableResolver = $priceTableResolver ? : ObjectManager::getInstance()->get(
            IndexScopeResolverInterface::class
        );
        $this->httpContext        = $httpContext ? : ObjectManager::getInstance()->get(Context::class);
    }

    public function apply(FilterInterface $filter, Select $select): bool
    {
        if (!in_array($filter->getField(), $this->validFields, true)) {
            return false;
        }

        if ($filter->getField() === 'price') {
            return $this->applyPriceFilter($filter, $select);
        } elseif ($filter->getField() === 'category_ids') {
            return $this->applyCategoryFilter($filter, $select);
        }
    }

    private function applyPriceFilter(FilterInterface $filter, Select $select): bool
    {
        $alias          = $this->aliasResolver->getAlias($filter);
        $websiteId      = $this->storeManager->getWebsite()->getId();
        $tableName      = $this->resourceConnection->getTableName('catalog_product_index_price');
        $mainTableAlias = $this->extractTableAliasFromSelect($select);

        $select->joinInner(
            [
                $alias => $tableName,
            ],
            $this->resourceConnection->getConnection()->quoteInto(
                sprintf('%s.entity_id = price_index.entity_id AND price_index.website_id = ?', $mainTableAlias),
                $websiteId
            ),
            []
        );

        return true;
    }

    private function applyCategoryFilter(FilterInterface $filter, Select $select): bool
    {
        $alias = $this->aliasResolver->getAlias($filter);

        $catalogCategoryProductDimension = new Dimension(
            \Magento\Store\Model\Store::ENTITY,
            $this->storeManager->getStore()->getId()
        );

        $tableName      = $this->tableResolver->resolve(
            AbstractAction::MAIN_INDEX_TABLE,
            [
                $catalogCategoryProductDimension,
            ]
        );
        $mainTableAlias = $this->extractTableAliasFromSelect($select);

        $select->joinInner(
            [
                $alias => $tableName,
            ],
            $this->resourceConnection->getConnection()->quoteInto(
                sprintf(
                    '%s.entity_id = category_ids_index.product_id AND category_ids_index.store_id = ?',
                    $mainTableAlias
                ),
                $this->storeManager->getStore()->getId()
            ),
            []
        );

        return true;
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
