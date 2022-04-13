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

namespace Mirasvit\SearchMysql\SearchAdapter\Query;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Registry;
use Magento\Framework\Search\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;

class Sorter
{
    private $attributeRepository;

    private $storeManager;

    private $resource;

    private $registry;

    private $tableMaintainer;

    public function __construct(
        ProductAttributeRepositoryInterface $attributeRepository,
        StoreManagerInterface $storeManager,
        ResourceConnection $resource,
        Registry $registry,
        TableMaintainer $tableMaintainer
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->storeManager        = $storeManager;
        $this->resource            = $resource;
        $this->registry            = $registry;
        $this->tableMaintainer     = $tableMaintainer;
    }

    public function process(Select $select, RequestInterface $request): void
    {
        if ($request->getIndex() !== 'catalogsearch_fulltext') {
            $select->order('score ' . Select::SQL_DESC)
                ->order('entity_id ' . Select::SQL_DESC);

            return;
        }
        foreach ($request->getSort() as $sort) {
            $field     = empty($sort['field'])?'relevance': $sort['field'];
            $direction = empty($sort['direction'])?'DESC': $sort['direction'];

            if ($field === 'relevance') {
                $select->order('score ' . Select::SQL_DESC)
                    ->order('entity_id ' . Select::SQL_DESC);
            } else {
                $this->sortCollection($select, $field, $direction);
            }
        }
    }

    private function sortCollection(Select $select, string $field, string $direction): void
    {
        $field = $this->joinAttribute($select, $field);

        if ($field) {
            $select->reset(\Zend_Db_Select::ORDER);
            $select->order(new \Zend_Db_Expr($field . ' ' . $direction));
        }
    }

    private function joinAttribute(Select $select, string $attributeCode): ?string
    {
        $storeId    = (int)$this->storeManager->getStore()->getId();
        $websiteId  = (int)$this->storeManager->getWebsite()->getId();
        $tableAlias = 'mst_search_' . $attributeCode;

        if ($attributeCode == 'position') {
            if (!$this->registry->registry('current_category')) {
                return null;
            }

            $this->joinTable(
                $select,
                $tableAlias,
                $this->tableMaintainer->getMainTable($storeId),
                [
                    "{$tableAlias}.product_id = search_index.entity_id",
                    "{$tableAlias}.store_id = {$storeId}",
                    "{$tableAlias}.category_id = " . (int)$this->registry->registry('current_category')->getId(),
                ]
            );

            return $tableAlias . '.position';
        } elseif ($attributeCode == 'price') {
            $this->joinTable(
                $select,
                $tableAlias,
                $this->resource->getTableName('catalog_product_index_price'),
                [
                    "{$tableAlias}.entity_id = search_index.entity_id",
                    "{$tableAlias}.website_id = {$websiteId}",
                    "{$tableAlias}.customer_group_id = 0",
                ]
            );

            return $tableAlias . '.min_price';
        } elseif ($attributeCode == 'sku') {
            $this->joinTable(
                $select,
                $tableAlias,
                $this->resource->getTableName('catalog_product_entity'),
                [
                    "{$tableAlias}.entity_id = search_index.entity_id",
                ]
            );

            return $tableAlias . '.sku';
        }

        try {
            $attribute = $this->attributeRepository->get($attributeCode);
        } catch (\Exception$e) {
            return null;
        }

        if ($attribute->getBackend()->isStatic()) {
            return 'search_index.' . $attributeCode;
        }

        $identifierField = 'entity_id';

        if (!$this->resource->getConnection()->tableColumnExists($attribute->getBackend()->getTable(), 'entity_id')) {
            $identifierField = 'row_id';
        }

        $this->joinTable(
            $select,
            $tableAlias . '_store',
            $attribute->getBackend()->getTable(),
            [
                "search_index.entity_id = {$tableAlias}_store.$identifierField",
                "{$tableAlias}_store.attribute_id = " . (int)$attribute->getId(),
                "{$tableAlias}_store.store_id = {$storeId}",
            ]
        );

        $this->joinTable(
            $select,
            $tableAlias . '_global',
            $attribute->getBackend()->getTable(),
            [
                "search_index.entity_id = {$tableAlias}_global.$identifierField",
                "{$tableAlias}_global.attribute_id = " . (int)$attribute->getId(),
                "{$tableAlias}_global.store_id = 0",
            ]
        );

        return 'IFNULL(' . $tableAlias . '_store.value, ' . $tableAlias . '_global.value)';
    }

    private function joinTable(Select $select, string $alias, string $name, array $conditions): void
    {
        foreach ($select->getPart(\Zend_Db_Select::FROM) as $aliasName => $item) {
            if ($item['tableName'] === $name && $aliasName === $alias) {
                return;
            }
        }

        $select->joinLeft([$alias => $name], implode(' AND ', $conditions), []);
    }
}
