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

namespace Mirasvit\LayeredNavigation\Model\DataMapper;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Mirasvit\LayeredNavigation\Model\Config\ExtraFilterConfigProvider;

class OnSaleDataMapper
{
    private $productCollectionFactory;

    private $extraFilterConfigProvider;

    private $resource;

    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        ExtraFilterConfigProvider $extraFilterConfigProvider,
        ResourceConnection $resource
    ) {
        $this->productCollectionFactory  = $productCollectionFactory;
        $this->extraFilterConfigProvider = $extraFilterConfigProvider;
        $this->resource                  = $resource;
    }

    public function map(array $documents, int $storeId): array
    {
        if (!$this->extraFilterConfigProvider->isOnSaleFilterEnabled()) {
            return $documents;
        }

        $rows = $this->resource->getConnection()->fetchPairs(
            $this->buildSelectQuery($storeId, array_keys($documents))
        );

        foreach ($documents as $id => &$doc) {
            $value = isset($rows[$id]) ? (int)$rows[$id] : 0;

            $doc[ExtraFilterConfigProvider::ON_SALE_FILTER]          = $value;
            $doc[ExtraFilterConfigProvider::ON_SALE_FILTER . '_raw'] = $value;
        }

        return $documents;
    }

    private function buildSelectQuery(int $storeId, array $productIds): Select
    {
        $productCollection = $this->productCollectionFactory->create()
            ->setStore($storeId)
            ->addStoreFilter($storeId)
            ->addPriceData();

        $productCollection->getSelect()
            ->where('price_index.final_price <> price_index.price')
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns('e.entity_id');

        $collectionQuery = $productCollection->getSelect()->assemble();

        $select = $this->resource->getConnection()->select()->union(
            [
                new \Zend_Db_Expr($collectionQuery),
                new \Zend_Db_Expr('SELECT parent_id as entity_id from ' . $this->resource->getTableName('catalog_product_relation') . '
                    where child_id in (' . $collectionQuery . ')'),
            ],
            Select::SQL_UNION
        );


        $derivedTable = $this->resource->getConnection()->select();
        $derivedTable->from(
            ['primary_table' => $this->resource->getTableName('catalog_product_entity')],
            []
        );

        $derivedTable->joinLeft(
            ['outer_table' => $select],
            'outer_table.entity_id  = primary_table.entity_id',
            [
                'entity_id' => 'primary_table.entity_id',
                'value'     => new \Zend_Db_Expr('IF(outer_table.entity_id IS NULL, 0, 1)'),
            ]
        );

        $derivedTable->where('primary_table.entity_id IN(?)', $productIds);

        return $derivedTable;
    }
}
