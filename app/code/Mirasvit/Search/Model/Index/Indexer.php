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



namespace Mirasvit\Search\Model\Index;

use Magento\CatalogSearch\Model\Indexer\IndexerHandlerFactory;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\Registry;
use Magento\Framework\Search\Request\Dimension;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Search\Api\Data\Index\InstanceInterface;
use Mirasvit\Search\Api\Data\IndexInterface;

class Indexer
{
    private $instance;

    private $storeManager;

    private $indexHandlerFactory;

    private $indexScopeResolver;

    private $registry;

    public function __construct(
        StoreManagerInterface $storeManager,
        IndexerHandlerFactory $indexHandlerFactory,
        IndexScopeResolver $indexScopeResolver,
        Registry $registry
    ) {
        $this->storeManager        = $storeManager;
        $this->indexHandlerFactory = $indexHandlerFactory;
        $this->indexScopeResolver  = $indexScopeResolver;
        $this->registry            = $registry;
    }

    public function getInstance(): InstanceInterface
    {
        return $this->instance;
    }

    public function setInstance(InstanceInterface $instance): Indexer
    {
        $this->instance = $instance;

        return $this;
    }

    public function getIndexName(int $storeId): string
    {
        $dimension = new Dimension('scope', $storeId);

        return $this->indexScopeResolver->resolve($this->instance->getIndexName(), [$dimension]);
    }

    public function reindexAll(int $storeId = null): bool
    {
        $instance = $this->getInstance();

        $configData = [
            'indexer_id' => $instance->getIdentifier(),
        ];

        $indexIdentifier = $instance->getIdentifier() == 'catalogsearch_fulltext'
            ? \Magento\Elasticsearch\Model\Config::ELASTICSEARCH_TYPE_DEFAULT
            : $instance->getIdentifier();

        //currently processed index, used for field & data mappings
        $this->registry->unregister(IndexInterface::IDENTIFIER);
        $this->registry->register(IndexInterface::IDENTIFIER, $indexIdentifier, true);

        /** @var \Magento\Elasticsearch\Model\Indexer\IndexerHandler $indexHandler */
        $indexHandler = $this->indexHandlerFactory->create(['data' => $configData]);

        $storeIds = array_keys($this->storeManager->getStores());
        foreach ($storeIds as $id) {
            if ($storeId && $storeId != $id) {
                continue;
            }

            $dimension = new Dimension('scope', $id);
            $indexHandler->cleanIndex([$dimension]);

            $indexHandler->saveIndex(
                [$dimension],
                $this->rebuildStoreIndex((int)$id)
            );
        }

        $this->registry->unregister(IndexInterface::IDENTIFIER);

        return true;
    }

    public function rebuildStoreIndex(int $storeId, array $ids = [])
    {
        $pk = $this->instance->getPrimaryKey();

        $attributes = array_keys($this->instance->getAttributeWeights());

        $lastEntityId = 0;

        while (true) {
            $documentData = $this->instance->getIndexableDocuments($storeId, $ids, $lastEntityId);
            if (count($documentData) == 0) {
                break;
            }

            foreach ($documentData as $data) {
                $document   = [];
                $documentId = (int)$data[$pk];

                foreach ($attributes as $attribute) {
                    $attributeId    = $this->instance->getAttributeId($attribute);
                    $attributeValue = $data[$attribute] ?? '';

                    $document[$attributeId] = $attributeValue;
                }

                yield $documentId => $document;

                $lastEntityId = $documentId;
            }
        }
    }
}
