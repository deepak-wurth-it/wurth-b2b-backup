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



namespace Mirasvit\Search\Controller\Adminhtml\Validator;

use Mirasvit\Search\Controller\Adminhtml\AbstractValidator;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Search\Repository\IndexRepository;
use Mirasvit\Search\Model\ConfigProvider;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;
use Magento\Backend\App\Action\Context;
use Mirasvit\Search\Api\Data\IndexInterface;
use Magento\Framework\Api\Search\Document;

class Validate extends AbstractValidator
{
    private $resultJsonFactory;

    private $storeManager;

    private $indexRepository;

    private $config;

    private $connectionManager;

    private $searchIndexNameResolver;

    private $status = self::STATUS_ERROR;

    private $searchTerm = null;

    private $entityId = null;

    private $result = [];

    private $indexes = [];

    private $items = [];

    public function __construct(
        JsonFactory $resultJsonFactory,
        StoreManagerInterface $storeManager,
        IndexRepository $indexRepository,
        ConfigProvider $config,
        ConnectionManager $connectionManager,
        SearchIndexNameResolver $searchIndexNameResolver,
        Context $context
    ) {
        $this->resultJsonFactory        = $resultJsonFactory;
        $this->storeManager             = $storeManager;
        $this->indexRepository          = $indexRepository;
        $this->config                   = $config;
        $this->connectionManager        = $connectionManager;
        $this->searchIndexNameResolver  = $searchIndexNameResolver;

        parent::__construct($context);
    }

    public function execute()
    {
        $response = $this->resultJsonFactory->create();

        if (!$this->prepareRequestData()) {
            return $response->setData(['result' => implode('', $this->result), 'items' => [], 'status' => $this->status]);
        }

        if ($this->entityId !== null) {
            if ($this->searchTerm !== null) {
                $this->result [] = "<p>All index data for Entity Id '{$this->entityId}' and Search Term '{$this->searchTerm}'</p><br />";
            } else {
                $this->result [] = "<p>All index data for Entity Id '{$this->entityId}'</p><br />";
            }

            $this->validateByEntityId();

            if (empty($this->items)) {
                $this->result [] = "<p>Nothing found for Entity Id</p><br />";
            }
        } else {
            $this->result [] = "<p>All search results for Search Term '{$this->searchTerm}'</p><br />";
            $this->validateBySearchTerm();
        }

        if (empty($this->items)) {
            $this->result [] = "<p>Nothing found for Search Term</p><br />";
        } elseif (!empty($this->items) && $this->status == self::STATUS_ERROR) {
            $this->result [] = "<p>Nothing found for Search Term, returned results for Entity Id</p><br />";
        }

        return $response->setData(['result' => implode('', $this->result), 'items' => $this->items, 'status' => $this->status]);
    }

    private function validateByEntityId(): void
    {
        foreach ($this->getIndexes() as $index) {
            foreach ($this->getEntityIdCollection($index) as $item) {
                $itemIndex = $item['_index'];
                unset($item['_index']);
                $item = str_replace('\n', ' ', json_encode($item));
                $this->items[$itemIndex][] = json_decode($this->highlightMatches($item));
            }
        }
    }

    private function validateBySearchTerm(): void
    {
        foreach ($this->getIndexes() as $index) {
            foreach ($this->getIndexQueryCollection($index) as $item) {
                if ($item === null) {
                    $title = $index->getTitle();
                    $this->result [] = "<p><b>Missing '". $title ."' search index.</b> Please run search reindex</p><br />";
                    continue;
                }
                $itemIndex = $item['_index'];
                unset($item['_index']);
                $item = str_replace('\n', ' ', json_encode($item));
                $this->items[$itemIndex][] = json_decode($this->highlightMatches($item));
            }
        }
    }

    private function prepareRequestData(): bool
    {
        if ($this->getRequest()->getParam('q') && !empty(trim($this->getRequest()->getParam('q')))) {
            $this->searchTerm = $this->getRequest()->getParam('q');
        }

        if ($this->getRequest()->getParam('entity_id') && !empty(trim($this->getRequest()->getParam('entity_id')))) {
            $this->entityId = $this->getRequest()->getParam('entity_id');
        }

        if ($this->searchTerm === null && $this->entityId === null) {
            $this->result[] = '<p>Please specify at least one parameter of the following : Search term, Entity Id </p>';

            return false;
        }

        if ($this->config->getEngine() != 'elasticsearch7') {
            $this->result[] = '<p>Validation for '. $this->config->getEngine() .' engine is not supported</p>';

            return false;
        }

        return true;
    }

    private function getIndexQueryCollection(IndexInterface $index): iterable
    {
        /** @var \Magento\Elasticsearch7\Model\Client\Elasticsearch $connection */
        $connection = $this->connectionManager->getConnection();

        if (!$connection->indexExists($this->getIndexName($index))) {
            return [null];
        }

        $result = $connection->query($this->prepareSearchTermQuery($index));

        return $this->processResponse($result);
    }

    private function getEntityIdCollection(IndexInterface $index): iterable
    {
        /** @var \Magento\Elasticsearch7\Model\Client\Elasticsearch $connection */
        $connection = $this->connectionManager->getConnection();

        if (!$connection->indexExists($this->getIndexName($index))) {
            return null;
        }

        $result = $connection->query($this->prepareEntityIdQuery($index));

        return $this->processResponse($result);
    }

    private function highlightMatches(string $data = null): ?string
    {
        if ($data === null || $this->searchTerm === null) {
            $this->status = self::STATUS_SUCCESS;
            return $data;
        }

        $data = strip_tags($data);
        $terms = explode(' ', $this->searchTerm);

        foreach ($terms as $term) {
            $data = str_ireplace($term, '<strong>'. $term .'</strong>', $data);
            if (strripos($data ,'<strong>'. $term .'</strong>') !== false) {
                $this->status = self::STATUS_SUCCESS;
            }

        }


        return $data;
    } 

    private function getIndexes(): array
    {
        if (empty($this->indexes)) {
            $result = [];

            $collection = $this->indexRepository->getCollection()
                ->addFieldToFilter(IndexInterface::IS_ACTIVE, 1)
                ->setOrder(IndexInterface::POSITION, 'asc');

            foreach ($collection as $index) {
                $index = $this->indexRepository->get($index->getId());

                if ($this->config->isMultiStoreModeEnabled()
                    && $index->getIdentifier() == 'catalogsearch_fulltext'
                ) {
                    foreach ($this->storeManager->getStores(false, true) as $code => $store) {
                        if (in_array($store->getId(), $this->config->getEnabledMultiStores())) {
                            $clone = clone $index;
                            $clone->setData('store_id', $store->getId());
                            $clone->setData('store_code', $code);
                            if ($this->storeManager->getStore()->getId() != $store->getId()) {
                                $clone->setData('title', $store->getName());
                            }
                            $result[] = $clone;
                        }
                    }
                } else {
                    $result[] = $index;
                }
            }
            $this->indexes = $result;
        }

        return $this->indexes;
    }

    private function getIndexName(IndexInterface $index): string
    {
        $indexName = $this->searchIndexNameResolver->getIndexName(
            $this->storeManager->getStore()->getId(),
            $index->getIdentifier()
        );

        return $indexName;
    }

    private function prepareEntityIdQuery(IndexInterface $index): array
    {
        return [
            'index' => $this->getIndexName($index),
            'body'  => [
                'from' => 0,
                'size' => 10,
                'stored_fields' => [
                    '_id',
                    '_score',
                    '_source',
                ],
                'sort' => [
                    ['_score' => ['order' => 'desc']]
                ],
                'query' => [
                    'terms' => [
                        '_id' => [
                            $this->entityId
                        ],
                    ],
                ],
            ],
        ];
    }

    private function prepareSearchTermQuery(IndexInterface $index): array {
        return [
            'index' => $this->getIndexName($index),
            'body'  => [
                'from' => 0,
                'size' => 10,
                'stored_fields' => [
                    '_id',
                    '_source',
                ],
                'sort' => [
                    ['_score' => ['order' => 'desc']]
                ],
                'query' => [
                    'bool' => [
                        'should' => [
                            ['wildcard' => [
                                '_search' => [
                                    'value' => "*$this->searchTerm*",
                                    'boost' => 1,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function processResponse(array $response): ?array
    {
        $result = null;
        if (isset($response['hits']['hits'])) {
            $result = $response['hits']['hits'];
            foreach ($result as $key => $item) {
                unset($result[$key]['_type']);
                unset($result[$key]['_score']);
            }
        }

        return $result;
    }
}
