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

namespace Mirasvit\SearchElastic\InstantProvider;

use Elasticsearch\Client;
use Mirasvit\SearchAutocomplete\InstantProvider\InstantProvider;

class EngineProvider extends InstantProvider
{
    private $query          = [];

    private $activeFilters  = [];

    private $applyFilter    = false;

    private $filtersToApply = [];

    private $searchTerms = [];

    public function getResults(string $indexIdentifier): array
    {
        $this->query = [
            'index' => $this->configProvider->getIndexName($indexIdentifier),
            'body'  => [
                'from'          => 0,
                'size'          => $this->getLimit($indexIdentifier),
                'stored_fields' => [
                    '_id',
                    '_score',
                    '_source',
                ],
                'sort'          => [
                    [
                        '_score' => [
                            'order' => 'desc',
                        ],
                    ],
                ],
                'query'         => [
                    'bool' => [
                        'minimum_should_match' => 1,
                    ],
                ],
            ],
            'track_total_hits' => true,
        ];

        $this->setMustCondition($indexIdentifier);
        $this->setShouldCondition($indexIdentifier);

        if ($indexIdentifier === 'catalogsearch_fulltext') {
            $this->setBuckets();
        }

        try {
            $rawResponse = $this->getClient()->search($this->query);
        } catch (\Exception $e) {
            return [
                'totalItems' => 0,
                'items'      => [],
                'buckets'    => [],
            ];
        }

        if ($this->configProvider->getEngine() == 'elasticsearch6') {
            $totalItems = (int)$rawResponse['hits']['total'];
        } else {
            $totalItems = (int)$rawResponse['hits']['total']['value'];
        }

        $items = [];

        foreach ($rawResponse['hits']['hits'] as $data) {
            if (!isset($data['_source']['_instant'])) {
                continue;
            }

            if (!$data['_source']['_instant']) {
                continue;
            }

            $items[] = $data['_source']['_instant'];
        }

        $buckets = [];

        if (isset($rawResponse['aggregations'])) {
            foreach ($rawResponse['aggregations'] as $code => $data) {
                $bucketData = $this->configProvider->getBucketOptionsData($code, $data['buckets']);
                if (empty($bucketData)) {
                    continue;
                }

                $buckets[$code] = $bucketData;
            }
        }

        if (!empty($this->getActiveFilters()) && $this->applyFilter == false) {
            $this->applyFilter = true;
            foreach ($this->getActiveFilters() as $filterKey => $value) {
                $this->filtersToApply[] = $filterKey;

                $result = $this->getResults($indexIdentifier);
                foreach ($result['buckets'] as $bucketKey => $bucket) {
                    if (in_array($bucketKey, $this->filtersToApply)) {
                        continue;
                    }

                    $buckets[$bucketKey] = $bucket;
                }

                $totalItems = $result['totalItems'];
                $items      = $result['items'];

            }
        }

        return [
            'totalItems' => count($items) > 0 ? $totalItems : 0,
            'items'      => $items,
            'buckets'    => $buckets,
        ];
    }

    private function getActiveFilters(): array
    {
        if (empty($this->activeFilters)) {
            $this->activeFilters = $this->configProvider->getActiveFilters();
        }

        if (!empty($this->filtersToApply)) {
            return array_intersect_key($this->activeFilters, array_flip($this->filtersToApply));
        }

        return $this->activeFilters;
    }

    private function setMustCondition(string $indexIdentifier): void
    {
        if ($indexIdentifier === 'catalogsearch_fulltext') {
            $this->query['body']['query']['bool']['must'][] = [
                'terms' => [
                    'visibility' => ['3', '4'],
                ],
            ];

            if ($this->applyFilter) {
                foreach ($this->getActiveFilters() as $filterCode => $filterValue) {
                    if ($filterCode == 'price') {
                        $this->query['body']['query']['bool']['must'][] = [
                            'range' => [
                                'price_0_1' => json_decode($filterValue),
                            ],
                        ];
                    } else {
                        $this->query['body']['query']['bool']['must'][] = [
                            'term' => [
                                $filterCode => $filterValue,
                            ],
                        ];
                    }
                }
            }
        }
    }

    private function setShouldCondition(string $indexIdentifier): void
    {
        $fields          = $this->configProvider->getIndexFields($indexIdentifier);
        $fields['_misc'] = 1;

        $searchQuery = $this->queryService->build($this->getQueryText());
        $selectQuery   = [];

        if (!isset($selectQuery['bool'])) {
            $selectQuery['bool'] = ['must' => []];
        }

        if (!$this->isKeyExists($selectQuery['bool']['must'], 'query_string')) {
            $preparedFields = [];
            foreach ($fields as $field => $weight) {
                $preparedFields[] = $field .'^'. $weight;
            }

            $selectQuery['bool']['must'][]['query_string'] = [
                'query'  => $this->compileQuery($searchQuery),
                'fields' => $preparedFields,
            ];

            $this->searchTerms = array_unique(array_filter($this->searchTerms));
            if (array_search('|NOT|', $this->searchTerms) !== false) {
                $this->searchTerms = array_slice($this->searchTerms, 0, array_search('|NOT|', $this->searchTerms));
            }

            foreach ($fields as $field => $boost) {
                foreach ($this->searchTerms as $term) {
                    if (strlen($term) == 1) {
                        $selectQuery['bool']['should'][]['wildcard'][$field] = [
                            'value' => $term,
                            'boost' => $boost * 0.75,
                        ];
                    } else {
                        $selectQuery['bool']['should'][]['wildcard'][$field] = [
                            'value' => $term,
                            'boost' => $boost,
                        ];
                    }
                }
            }
        }

        if (!isset($this->query['body']['query']['bool']['should'])) {
            $this->query['body']['query']['bool']['should'] = [];
        }

        $this->query['body']['query']['bool']['should'] = array_merge(
            $this->query['body']['query']['bool']['should'],
            $selectQuery['bool']['should']
        );

        if (!isset($this->query['body']['query']['bool']['must'])) {
            $this->query['body']['query']['bool']['must'] = [];
        }

        $this->query['body']['query']['bool']['must'] = array_merge(
            $this->query['body']['query']['bool']['must'],
            $selectQuery['bool']['must']
        );
    }

    private function isKeyExists(array $array, string $keySearch): bool
    {
        if (array_key_exists($keySearch, $array)) {
            return true;
        } else {
            foreach ($array as $key => $item) {
                if (is_array($item) && $this->isKeyExists($item, $keySearch)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function setBuckets(): void
    {
        foreach ($this->getBuckets() as $fieldName) {
            if ($this->applyFilter && in_array($fieldName, $this->filtersToApply)) {
                continue;
            }

            if ($fieldName == 'price') {
                $this->query['body']['aggregations'][$fieldName] = ['terms' => ['field' => 'price_0_1', 'size' => 500]];
            } else {
                $this->query['body']['aggregations'][$fieldName] = ['terms' => ['field' => $fieldName, 'size' => 500]];
            }
        }
    }

    private function compileQuery(array $query): string
    {
        $compiled = [];
        foreach ($query as $directive => $value) {
            switch ($directive) {
                case '$like':
                    $compiled[] = '(' . $this->compileQuery($value) . ')';
                    break;

                case '$!like':
                    $this->searchTerms[] = '|NOT|';
                    $compiled[] = '(NOT ' . $this->compileQuery($value) . ')';
                    break;

                case '$and':
                    $and = [];
                    foreach ($value as $item) {
                        $and[] = $this->compileQuery($item);
                    }
                    $compiled[] = '(' . implode(' AND ', $and) . ')';
                    break;

                case '$or':
                    $or = [];
                    foreach ($value as $item) {
                        $or[] = $this->compileQuery($item);
                    }
                    $compiled[] = '(' . implode(' OR ', $or) . ')';
                    break;

                case '$term':
                    $phrase = $this->escape($value['$phrase']);
                    switch ($value['$wildcard']) {
                        case $this->configProvider::WILDCARD_INFIX:
                            $compiled[] = "$phrase^2 OR *$phrase*";
                            $this->searchTerms[] = $phrase;
                            $this->searchTerms[] = "*$phrase*";
                            break;
                        case $this->configProvider::WILDCARD_PREFIX:
                            $compiled[] = "$phrase^2 OR *$phrase";
                            $this->searchTerms[] = $phrase;
                            $this->searchTerms[] = "*$phrase";
                            break;
                        case $this->configProvider::WILDCARD_SUFFIX:
                            $compiled[] = "$phrase^2 OR $phrase*";
                            $this->searchTerms[] = $phrase;
                            $this->searchTerms[] = "$phrase*";
                            break;
                        case $this->configProvider::WILDCARD_DISABLED:
                            $compiled[] = $phrase .'^2';
                            $this->searchTerms[] = $phrase;
                            break;
                    }
                    break;
            }
        }

        return implode(' AND ', $compiled);
    }

    private function getClient(): Client
    {
        return \Elasticsearch\ClientBuilder::fromConfig($this->configProvider->getEngineConnection(), true);
    }
}
