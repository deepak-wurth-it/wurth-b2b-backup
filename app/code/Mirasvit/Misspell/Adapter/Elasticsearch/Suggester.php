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

namespace Mirasvit\Misspell\Adapter\Elasticsearch;

use Mirasvit\Misspell\Api\Data\SuggestInterface;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;
use Magento\Store\Model\StoreManagerInterface;

class Suggester implements SuggestInterface
{
    private $connectionManager;

    private $searchIndexNameResolver;

    private $storeManager;

    private $indexName = null;

    public function __construct(
        ConnectionManager $connectionManager,
        SearchIndexNameResolver $searchIndexNameResolver,
        StoreManagerInterface $storeManager
    ) {
        $this->connectionManager       = $connectionManager;
        $this->searchIndexNameResolver = $searchIndexNameResolver;
        $this->storeManager            = $storeManager;
    }

    public function suggest(string $query): ?string
    {
        $query = preg_split('/[\s]+/', $query);
        /** @var \Magento\Elasticsearch7\Model\Client\Elasticsearch $connection */
        $connection = $this->connectionManager->getConnection();

        if (!$connection->indexExists($this->getIndexName())) {
            return null;
        }

        $response   = [];

        if (!is_array($query)) {
            $query = [$query];
        }

        foreach ($query as $term) {
            $result = $connection->query($this->prepareTermSuggestQuery($term));

            $processedResponse = $this->processResponse($result);

            if (empty($processedResponse)) {
                $result = $connection->query($this->preparePhraseSuggestQuery($term));
                $processedResponse = $this->processResponse($result);
            }

            $response[] = $processedResponse;
        }

        $response = array_filter($response);
        $response = array_unique($response);

        if (empty($response)) {
            return null;
        }

        return implode(' ', $response);
    }

    private function prepareTermSuggestQuery(string $query): array
    {
        return [
            'index' => $this->getIndexName(),
            'body'  => [
                'suggest' => [
                    'suggestion' => [
                        'text'       => $query,
                        'term' => [
                            'field'            => 'keyword',
                            'size'             => 1,
                            'prefix_length'    => 0,
                        ],
                    ],
                ],
            ],
        ];
    }

    private function preparePhraseSuggestQuery(string $query): array
    {
        return [
            'index' => $this->getIndexName(),
            'body'  => [
                'suggest' => [
                    'text'       => $query,
                    'suggestion' => [
                        'phrase' => [
                            'field'            => 'keyword.trigram',
                            'size'             => 1,
                            'gram_size'        => 3,
                            'max_errors'       => 100,
                            'direct_generator' => [
                                [
                                    'field'        => 'keyword.trigram',
                                    'suggest_mode' => 'always',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function getIndexName(): string
    {
        if ($this->indexName === null) {
            $this->indexName = $this->searchIndexNameResolver->getIndexName(
                $this->storeManager->getStore()->getId(),
                'mst_misspell_index'
            );
        }

        return $this->indexName;
    }

    private function processResponse(array $response): ?string
    {
        $result = null;
        if (isset($response['suggest']['suggestion'][0]['options'][0]['text'])) {
            $result = $response['suggest']['suggestion'][0]['options'][0]['text'];
        } else if (isset($response['suggest']['suggestion'][0]['text'])) {
            $result = $response['suggest']['suggestion'][0]['text'];
        }

        return $result;
    }
}
