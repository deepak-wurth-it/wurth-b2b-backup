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



namespace Mirasvit\SearchSphinx\Model\Indexer;

use Magento\Elasticsearch\Model\Adapter\BatchDataMapperInterface;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\Search\Request\Dimension;
use Mirasvit\Search\Repository\IndexRepository;
use Mirasvit\SearchMysql\SearchAdapter\Index\IndexNameResolver;
use Mirasvit\SearchSphinx\Model\Engine;

class IndexerHandler implements IndexerInterface
{
    private $indexRepository;

    private $batch;

    private $indexNameResolver;

    private $batchDocumentDataMapper;

    private $engine;

    private $data;

    private $batchSize;

    public function __construct(
        IndexRepository $indexRepository,
        Batch $batch,
        IndexNameResolver $indexNameResolver,
        BatchDataMapperInterface $batchDataMapper,
        Engine $engine,
        array $data,
        int $batchSize = 1000
    ) {
        $this->indexRepository         = $indexRepository;
        $this->indexNameResolver       = $indexNameResolver;
        $this->batch                   = $batch;
        $this->data                    = $data;
        $this->engine                  = $engine;
        $this->batchDocumentDataMapper = $batchDataMapper;
        $this->batchSize               = $batchSize;
    }

    /**
     * @param Dimension[] $dimensions
     */
    public function saveIndex($dimensions, \Traversable $documents): void
    {
        $index     = $this->indexRepository->getByIdentifier($this->getIndexName());
        $indexName = $this->indexNameResolver->getIndexName($this->getIndexName(), $dimensions);
        $dimension = current($dimensions);
        $scopeId   = (int)$dimension->getValue();

        foreach ($this->batch->getItems($documents, $this->batchSize) as $documentsBatch) {
            $docs = $this->prepareDocsPerStore($documentsBatch, $scopeId);
            $this->engine->saveDocuments($index, $indexName, $docs);
        }
    }

    public function prepareDocsPerStore(array $documentData, int $storeId): array
    {
        $documents = [];
        if (count($documentData)) {
            $documents = $this->batchDocumentDataMapper->map(
                $documentData,
                $storeId
            );
        }

        return $documents;
    }

    /**
     * @param Dimension[] $dimensions
     */
    public function deleteIndex($dimensions, \Traversable $documents): void
    {
        $index     = $this->indexRepository->getByIdentifier($this->getIndexName());
        $indexName = $this->indexNameResolver->getIndexName($this->getIndexName(), $dimensions);

        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $this->engine->deleteDocuments($index, $indexName, $batchDocuments);
        }
    }

    /**
     * @param Dimension[] $dimensions
     */
    public function cleanIndex($dimensions): void
    {
        $indexName = $this->indexNameResolver->getIndexName($this->getIndexName(), $dimensions);
        $this->engine->cleanIndex($indexName);
    }

    /**
     * @param Dimension[] $dimensions
     */
    public function isAvailable($dimensions = []): bool
    {
        return true;
    }

    private function getIndexName(): string
    {
        return $this->data['indexer_id'];
    }
}
