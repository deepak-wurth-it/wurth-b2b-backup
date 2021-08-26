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

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Mirasvit\LayeredNavigation\Model\Config\ExtraFilterConfigProvider;

class RatingDataMapper
{
    private $resource;

    private $extraFilterConfigProvider;

    public function __construct(
        ExtraFilterConfigProvider $extraFilterConfigProvider,
        ResourceConnection $resource
    ) {
        $this->extraFilterConfigProvider = $extraFilterConfigProvider;
        $this->resource                  = $resource;
    }

    public function map(array $documents, int $storeId): array
    {
        if (!$this->extraFilterConfigProvider->isRatingFilterEnabled()) {
            return $documents;
        }

        $rows = $this->resource->getConnection()->fetchPairs(
            $this->buildSelectQuery($storeId, array_keys($documents))
        );

        foreach ($documents as $id => &$doc) {
            $value = isset($rows[$id]) ? (int)$rows[$id] : 0;

            $doc[ExtraFilterConfigProvider::RATING_FILTER]          = $value;
            $doc[ExtraFilterConfigProvider::RATING_FILTER . '_raw'] = $value;
        }

        return $documents;
    }

    private function buildSelectQuery(int $storeId, array $productIds): Select
    {
        $derivedTable = $this->resource->getConnection()->select()->from(
            ['primary_table' => $this->resource->getTableName('catalog_product_entity')],
            []
        );

        $derivedTable->joinLeft(
            ['review_table' => $this->resource->getTableName('review_entity_summary')],
            'review_table.entity_pk_value = primary_table.entity_id AND review_table.entity_type = 1 AND review_table.store_id  = ' . $storeId,
            [
                'entity_id' => 'primary_table.entity_id',
                'value'     => new \Zend_Db_Expr('
                    IF (review_table.rating_summary >= 100, 5,
                        IF (review_table.rating_summary >=80, 4,
                            IF (review_table.rating_summary >=60, 3,
                                IF (review_table.rating_summary >=40, 2,
                                    IF (review_table.rating_summary >=20, 1, 0)
                                )
                            )
                        )
                    )'),
            ]
        );

        return $derivedTable;
    }
}
