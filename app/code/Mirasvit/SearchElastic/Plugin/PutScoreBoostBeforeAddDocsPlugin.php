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

namespace Mirasvit\SearchElastic\Plugin;

use Mirasvit\Search\Api\Data\ScoreRuleInterface;
use Magento\Elasticsearch\Model\Adapter\Elasticsearch;
use Magento\Framework\App\ResourceConnection;

/**
 * @SuppressWarnings(PHPMD)
 * @see \Magento\Elasticsearch\Model\Adapter\Elasticsearch::addDocs()
 */
class PutScoreBoostBeforeAddDocsPlugin
{
    private $resource;

    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    const EMPTY_SUM_VALUE = 0;
    const EMPTY_MULTIPLY_VALUE = 1;
    const SUM_ATTRIBUTE = 'mst_score_sum';
    const MULTIPLY_ATTRIBUTE = 'mst_score_multiply';

    public function beforeAddDocs(Elasticsearch $subject, array $docs, int $storeId, string $mappedIndexerId)
    {
        if ($mappedIndexerId == 'mst_misspell_index') {
            return [$docs, $storeId, $mappedIndexerId];
        }

        $productIds = array_keys($docs);
        $ids = [];

        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(['index' => $this->getIndexTable()], ['product_id', 'score_factor'])
            ->where('index.store_id IN (?)', [0, $storeId])
            ->where('index.product_id IN (?)', $productIds);

        $rows = $connection->fetchAll($select);

        $scoreFactors = [];
        array_map(function($row) use (&$scoreFactors) {$scoreFactors[$row['product_id']] = $row['score_factor'];}, $rows);

        foreach ($docs as $productId => $doc) {
            $docs[$productId][self::SUM_ATTRIBUTE] = self::EMPTY_SUM_VALUE;
            $docs[$productId][self::MULTIPLY_ATTRIBUTE] = self::EMPTY_MULTIPLY_VALUE;

            if (isset($scoreFactors[$productId])) {
                if (strripos($scoreFactors[$productId], '*') !== false) {
                    $docs[$productId][self::MULTIPLY_ATTRIBUTE] = (int) str_replace('*', '', $scoreFactors[$productId]);
                } else {
                    $docs[$productId][self::SUM_ATTRIBUTE] = (int) str_replace('+', '', $scoreFactors[$productId]);
                }
            }
        }

        return [$docs, $storeId, $mappedIndexerId];
    }

    private function getIndexTable()
    {
        return $this->resource->getTableName(ScoreRuleInterface::INDEX_TABLE_NAME);
    }
}
