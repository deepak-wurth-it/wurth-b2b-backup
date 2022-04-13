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



namespace Mirasvit\SearchMysql\Model\Indexer;

use Magento\Elasticsearch\Model\Adapter\BatchDataMapperInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Mirasvit\SearchMysql\SearchAdapter\Index\IndexNameResolver;

class IndexerHandler implements IndexerInterface
{
    private $resource;

    private $indexStructure;

    private $batchDocumentDataMapper;

    private $indexNameResolver;

    private $batch;

    private $scopeResolver;

    private $data;

    private $batchSize;

    public function __construct(
        ResourceConnection $resource,
        IndexStructure $indexStructure,
        BatchDataMapperInterface $batchDataMapper,
        IndexNameResolver $indexNameResolver,
        Batch $batch,
        ScopeResolverInterface $scopeResolver,
        array $data = [],
        int $batchSize = 500
    ) {
        $this->resource                = $resource;
        $this->indexStructure          = $indexStructure;
        $this->indexNameResolver       = $indexNameResolver;
        $this->batch                   = $batch;
        $this->data                    = $data;
        $this->batchSize               = $batchSize;
        $this->scopeResolver           = $scopeResolver;
        $this->batchDocumentDataMapper = $batchDataMapper;
    }

    public function saveIndex($dimensions, \Traversable $documents): void
    {
        $dimension = current($dimensions);
        $scopeId   = (int)$dimension->getValue();

        foreach ($this->batch->getItems($documents, $this->batchSize) as $documentsBatch) {
            $docs = $this->prepareDocsPerStore($documentsBatch, $scopeId);

            $this->insertDocuments($docs, $dimensions);
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

    public function deleteIndex($dimensions, \Traversable $documents): void
    {
        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $this->resource->getConnection()
                ->delete($this->getTableName($dimensions), ['entity_id in (?)' => $batchDocuments]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function cleanIndex($dimensions)
    {
        $this->indexStructure->delete($this->getIndexName(), $dimensions);
        $this->indexStructure->create($this->getIndexName(), [], $dimensions);
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable($dimensions = [])
    {
        if (empty($dimensions)) {
            return true;
        }

        return $this->resource->getConnection()->isTableExists($this->getTableName($dimensions));
    }

    private function insertDocuments(array $documents, array $dimensions): void
    {
        $documents = $this->prepareSearchableFields($documents);

        if (empty($documents)) {
            return;
        }
        $this->resource->getConnection()->insertOnDuplicate(
            $this->getTableName($dimensions),
            $documents,
            ['data_index']
        );
    }

    private function prepareSearchableFields(array $documents): array
    {
        $insertDocuments = [];
        foreach ($documents as $entityId => $document) {
            foreach ($document as $attributeCode => $fieldValue) {
                $insertDocuments[$entityId . '_' . $attributeCode] = [
                    'entity_id'      => $entityId,
                    'attribute_code' => $attributeCode,
                    'data_index'     => $fieldValue,
                ];
            }
        }

        return $insertDocuments;
    }

    private function getTableName(array $dimensions): string
    {
        return $this->indexNameResolver->getIndexName($this->getIndexName(), $dimensions);
    }

    private function getIndexName(): string
    {
        return $this->data['indexer_id'];
    }
}
