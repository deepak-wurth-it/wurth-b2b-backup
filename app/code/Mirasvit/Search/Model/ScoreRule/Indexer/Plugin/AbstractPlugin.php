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



namespace Mirasvit\Search\Model\ScoreRule\Indexer\Plugin;

use Magento\Framework\Indexer\IndexerRegistry;
use Mirasvit\Search\Model\ScoreRule\Indexer\ScoreRuleIndexer;

abstract class AbstractPlugin
{
    /** @var \Magento\Framework\Indexer\IndexerRegistry */
    protected $indexerRegistry;

    /**
     * AbstractPlugin constructor.
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        IndexerRegistry $indexerRegistry
    ) {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Reindex by product if indexer is not scheduled
     *
     * @param int $productId
     * @return void
     */
    protected function reindexRow($productId)
    {
        $indexer = $this->indexerRegistry->get(ScoreRuleIndexer::INDEXER_ID);
        if (!$indexer->isScheduled()) {
            $indexer->reindexRow($productId);
        }
    }

    /**
     * Reindex by product if indexer is not scheduled
     *
     * @param int[] $productIds
     * @return void
     */
    protected function reindexList(array $productIds)
    {
        $indexer = $this->indexerRegistry->get(ScoreRuleIndexer::INDEXER_ID);
        if (!$indexer->isScheduled()) {
            $indexer->reindexList($productIds);
        }
    }
}
