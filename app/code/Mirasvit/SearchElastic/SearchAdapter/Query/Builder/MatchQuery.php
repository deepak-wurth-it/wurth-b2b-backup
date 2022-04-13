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

namespace Mirasvit\SearchElastic\SearchAdapter\Query\Builder;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ResolverInterface as TypeResolver;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\Query\ValueTransformerPool;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Mirasvit\Search\Model\ConfigProvider;
use Mirasvit\Search\Service\QueryService;

class MatchQuery extends MatchCompatibility
{
    private $queryService;

    private $fieldMapper;

    private $attributeProvider;

    private $config;

    private $searchTerms = [];

    public function __construct(
        QueryService $queryService,
        FieldMapperInterface $fieldMapper,
        AttributeProvider $attributeProvider,
        TypeResolver $fieldTypeResolver,
        ValueTransformerPool $valueTransformerPool,
        Config $config
    ) {
        $this->queryService         = $queryService;
        $this->fieldMapper          = $fieldMapper;
        $this->attributeProvider    = $attributeProvider;
        $this->config               = $config;

        parent::__construct($fieldMapper, $attributeProvider, $fieldTypeResolver, $valueTransformerPool, $config);
    }

    /**
     * @param string $conditionType
     */
    public function build(array $selectQuery, RequestQueryInterface $requestQuery, $conditionType): array
    {
        $queryValue = $requestQuery->getValue();
        $searchQuery = $this->queryService->build($queryValue);
        $fields = [];

        foreach ($requestQuery->getMatches() as $match) {
            $boost = (int)($match['boost'] ?? 1);

            $resolvedField = $this->fieldMapper->getFieldName(
                $match['field'],
                ['type' => FieldMapperInterface::TYPE_QUERY]
            );

            if (in_array($resolvedField, ['links_purchased_separately'])) {
                continue;
            }

            if ($resolvedField === '_search') {
                $resolvedField = '_misc';
            }

            $fields[$resolvedField] = $boost;
        }

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

            $this->searchTerms = array_values(array_unique(array_filter($this->searchTerms)));

            if (array_search('|NOT|', $this->searchTerms) !== false) {
                $this->searchTerms = array_slice($this->searchTerms, 0, array_search('|NOT|', $this->searchTerms));
            }

            foreach ($fields as $field => $boost) {
                foreach ($this->searchTerms as $term) {
                    if (strlen($term) == 1) {
                        $selectQuery['bool']['should'][]['wildcard'][$field] = [
                            'value' => $term,
                            'boost' => (string) $boost * 0.75,
                        ];
                    } else {
                        $selectQuery['bool']['should'][]['wildcard'][$field] = [
                            'value' => $term,
                            'boost' => (string) $boost,
                        ];
                    }
                }
            }
        }

        return $selectQuery;
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
                        case ConfigProvider::WILDCARD_INFIX:
                            $compiled[] = "$phrase^2 OR *$phrase*";
                            $this->searchTerms[] = $phrase;
                            $this->searchTerms[] = "*$phrase*";
                            break;
                        case ConfigProvider::WILDCARD_PREFIX:
                            $compiled[] = "$phrase^2 OR *$phrase";
                            $this->searchTerms[] = $phrase;
                            $this->searchTerms[] = "*$phrase";
                            break;
                        case ConfigProvider::WILDCARD_SUFFIX:
                            $compiled[] = "$phrase^2 OR $phrase*";
                            $this->searchTerms[] = $phrase;
                            $this->searchTerms[] = "$phrase*";
                            break;
                        case ConfigProvider::WILDCARD_DISABLED:
                            $compiled[] = $phrase .'^2';
                            $this->searchTerms[] = $phrase;
                            break;
                    }
                    break;
            }
        }

        return implode(' AND ', $compiled);
    }

    private function escape(string $value): string
    {
        $pattern = '/(\+|-|\/|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';
        $replace = '\\\$1';

        return preg_replace($pattern, $replace, $value);
    }
}
