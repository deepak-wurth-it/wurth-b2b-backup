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



namespace Mirasvit\Search\Model\ScoreRule\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Mirasvit\Search\Api\Data\ScoreRuleInterface;
use Mirasvit\Search\Repository\ScoreRuleRepository;
use Mirasvit\Search\Ui\ScoreRule\Source\ScoreFactorRelatively;

class ScoreRuleIndexer implements IndexerActionInterface
{
    const INDEXER_ID = 'mirasvit_search_score_rule_product';

    const RULE_ID      = ScoreRuleInterface::ID;
    const STORE_ID     = 'store_id';
    const PRODUCT_ID   = 'product_id';
    const SCORE_FACTOR = ScoreRuleInterface::SCORE_FACTOR;


    private $resource;

    private $scoreRuleRepository;


    public function __construct(
        ResourceConnection $resource,
        ScoreRuleRepository $scoreRuleRepository
    ) {
        $this->resource            = $resource;
        $this->scoreRuleRepository = $scoreRuleRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function executeFull()
    {
        foreach ($this->scoreRuleRepository->getCollection() as $scoreRule) {
            $this->execute($scoreRule, []);
        }

        $this->executeZeroRule([]);
    }

    /**
     * @param ScoreRuleInterface $scoreRule
     * @param array              $productIds
     *
     * @throws \Zend_Db_Exception
     */
    public function execute(ScoreRuleInterface $scoreRule, array $productIds)
    {
        $connection = $this->resource->getConnection();

        $this->ensureIndexTable();

        // Real Score Rules
        foreach ($scoreRule->getStoreIds() as $storeId) {
            $storeId     = intval($storeId);
            $deleteWhere = [
                self::STORE_ID . ' = ' . $storeId,
                self::RULE_ID . ' = ' . $scoreRule->getId(),
            ];
            if ($productIds) {
                $deleteWhere[] = self::PRODUCT_ID . ' IN(' . implode(',', $productIds) . ')';
            }

            $connection->delete($this->getIndexTable(), $deleteWhere);

            $idx = 0;
            $ids = $scoreRule->getRule()->getMatchingProductIds($productIds);

            $scoreFactors = $this->getScoreFactors($scoreRule, $ids);

            do {
                $rows = [];

                for (; $idx < count($ids); $idx++) {
                    $row = [
                        self::RULE_ID      => $scoreRule->getId(),
                        self::STORE_ID     => $storeId,
                        self::PRODUCT_ID   => $ids[$idx],
                        self::SCORE_FACTOR => $scoreFactors[$ids[$idx]],
                    ];

                    $rows[] = $row;

                    if (count($rows) > 1000) {
                        break;
                    }
                }

                if (count($rows)) {
                    $connection->insertMultiple($this->getIndexTable(), $rows);
                }
            } while (count($rows));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function executeList(array $ids)
    {
        foreach ($this->scoreRuleRepository->getCollection() as $scoreRule) {
            $this->execute($scoreRule, $ids);
        }

        $this->executeZeroRule($ids);
    }

    /**
     * {@inheritdoc}
     */
    public function executeRow($id)
    {
        foreach ($this->scoreRuleRepository->getCollection() as $scoreRule) {
            $this->execute($scoreRule, [$id]);
        }

        $this->executeZeroRule([$id]);
    }

    /**
     * @return $this
     * @throws \Zend_Db_Exception
     */
    private function ensureIndexTable()
    {
        $tableName = $this->getIndexTable();

        $connection = $this->resource->getConnection();

        if ($connection->isTableExists($tableName)) {
            return $this;
        }

        $table = $connection->newTable($tableName);

        $table->addColumn(self::RULE_ID, Table::TYPE_INTEGER);
        $table->addColumn(self::STORE_ID, Table::TYPE_INTEGER);
        $table->addColumn(self::PRODUCT_ID, Table::TYPE_INTEGER);
        $table->addColumn(self::SCORE_FACTOR, Table::TYPE_TEXT);

        $connection->createTable($table);

        return $this;
    }

    /**
     * @return string
     */
    private function getIndexTable()
    {
        return $this->resource->getTableName(ScoreRuleInterface::INDEX_TABLE_NAME);
    }

    /**
     * @param ScoreRuleInterface $scoreRule
     * @param array              $productIds
     *
     * @return array
     */
    public function getScoreFactors(ScoreRuleInterface $scoreRule, array $productIds)
    {
        [$sign, $factor, $relatively] = explode('|', $scoreRule->getScoreFactor());

        $result = [];

        if ($relatively === ScoreFactorRelatively::RELATIVELY_POPULARITY) {
            foreach ($productIds as $productId) {
                $result[$productId] = '+0';
            }

            $connection = $this->resource->getConnection();
            $select     = $connection->select()->from(
                $this->resource->getTableName('sales_order_item'),
                ['product_id', 'cnt' => new \Zend_Db_Expr('count(*)')]
            )->group('product_id');
            $rows       = $connection->fetchAll($select);
            $max        = 0;
            foreach ($rows as $row) {
                if ($row['cnt'] > $max) {
                    $max = $row['cnt'];
                }
            }
            foreach ($rows as $row) {
                $result[$row['product_id']] = $sign . ($row['cnt'] / $max) * $factor;
            }
        } elseif ($relatively == ScoreFactorRelatively::RELATIVELY_RATING) {
            foreach ($productIds as $productId) {
                $result[$productId] = '+0';
            }

            $connection = $this->resource->getConnection();
            $select     = $connection->select()->from(
                $this->resource->getTableName('rating_option_vote'),
                ['product_id' => 'entity_pk_value', 'cnt' => new \Zend_Db_Expr('avg(percent)')]
            )->group('product_id');
            $rows       = $connection->fetchAll($select);
            $max        = 100;

            foreach ($rows as $row) {
                $result[$row['product_id']] = $sign . ($row['cnt'] / $max) * $factor;
            }
        } else {
            foreach ($productIds as $productId) {
                $result[$productId] = $sign . $factor;
            }
        }

        return $result;
    }

    /**
     * @param array $productIds
     *
     * @throws \Zend_Db_Exception
     */
    private function executeZeroRule(array $productIds)
    {
        $connection = $this->resource->getConnection();

        $this->ensureIndexTable();

        $deleteWhere = [
            self::STORE_ID . ' = 0',
            self::RULE_ID . ' = 0',
        ];
        if ($productIds) {
            $deleteWhere[] = self::PRODUCT_ID . ' IN(' . implode(',', $productIds) . ')';
        }

        $connection->delete($this->getIndexTable(), $deleteWhere);

        // Product Search Weight
        $select = $connection->select()->from(
            [$this->resource->getTableName('catalog_product_entity')],
            ['entity_id', 'mst_search_weight']
        )->where('(mst_search_weight > 0 or mst_search_weight < 0)');
        if ($productIds) {
            $select->where('entity_id IN(' . implode(',', $productIds) . ')');
        }

        $data = $connection->fetchAll($select);
        $idx  = 0;

        do {
            $rows = [];
            for (; $idx < count($data); $idx++) {
                $id     = $data[$idx]['entity_id'];
                $factor = $data[$idx]['mst_search_weight'];
                $row    = [
                    self::RULE_ID      => 0,
                    self::STORE_ID     => 0,
                    self::PRODUCT_ID   => $id,
                    self::SCORE_FACTOR => $factor > 0 ? '+' . $factor : $factor,
                ];

                $rows[] = $row;

                if (count($rows) > 1000) {
                    break;
                }
            }

            if (count($rows)) {
                $connection->insertMultiple($this->getIndexTable(), $rows);
            }
        } while (count($rows));
    }
}
