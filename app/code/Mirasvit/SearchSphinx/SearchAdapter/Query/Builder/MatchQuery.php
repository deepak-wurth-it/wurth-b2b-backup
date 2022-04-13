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



namespace Mirasvit\SearchSphinx\SearchAdapter\Query\Builder;

use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Mirasvit\Search\Model\ConfigProvider;
use Mirasvit\Search\Service\QueryService;
use Mirasvit\SearchSphinx\SearchAdapter\Field\FieldInterface;
use Mirasvit\SearchSphinx\SearchAdapter\Field\Resolver;
use Mirasvit\SearchSphinx\SphinxQL\Expression as QLExpression;
use Mirasvit\SearchSphinx\SphinxQL\SphinxQL;

class MatchQuery implements QueryInterface
{
    private $queryService;

    private $resolver;

    public function __construct(
        Resolver $resolver,
        QueryService $queryService
    ) {
        $this->resolver     = $resolver;
        $this->queryService = $queryService;
    }

    public function build(SphinxQL $select, RequestQueryInterface $query): SphinxQL
    {
        /** @var \Mirasvit\SearchMysql\SearchAdapter\MatchCompatibility $query */
        $fieldList = [];
        foreach ($query->getMatches() as $match) {
            $fieldList[] = $match['field'];
        }

        $resolvedFieldList = $this->resolver->resolve($fieldList);

        $fieldIds = [];
        $columns  = [];

        /** @var \Mirasvit\SearchSphinx\SearchAdapter\Field\Field $field */
        foreach ($resolvedFieldList as $field) {
            if ($field->getType() === FieldInterface::TYPE_FULLTEXT && $field->getAttributeId()) {
                $fieldIds[] = $field->getAttributeId();
            }

            $column = $field->getColumn();

            $columns[$column] = $column;

        }

        $searchQuery = $this->queryService->build($query->getValue());
        $matchQuery  = $this->compileQuery($searchQuery);

        $select->match($columns, new QLExpression($matchQuery));

        return $select;
    }

    private function compileQuery(array $query): string
    {
        $compiled = [];
        foreach ($query as $directive => $value) {
            switch ($directive) {
                case '$like':
                    $like = $this->compileQuery($value);
                    if ($like) {
                        $compiled[] = '(' . $like . ')';
                    }
                    break;

                case '$!like':
                    $notLike = $this->compileQuery($value);
                    if ($notLike) {
                        $compiled[] = '!(' . $notLike . ')';
                    }
                    break;

                case '$and':
                    $and = [];
                    foreach ($value as $item) {
                        $and[] = $this->compileQuery($item);
                    }
                    $and = array_filter($and);
                    if ($and) {
                        $compiled[] = '(' . implode(' ', $and) . ')';
                    }
                    break;

                case '$or':
                    $or = [];
                    foreach ($value as $item) {
                        $or[] = $this->compileQuery($item);
                    }
                    $or = array_filter($or);
                    $or = array_slice($or, 0, 3);
                    if ($or) {
                        $compiled[] = '(' . implode(' | ', $or) . ')';
                    }
                    break;

                case '$term':
                    $phrase = $this->escape($value['$phrase']);
                    if (strlen($phrase) == 1) {
                        if ($value['$wildcard'] == ConfigProvider::WILDCARD_DISABLED) {
                            $compiled[] = "$phrase";
                        } else {
                            $compiled[] = "$phrase*";
                        }
                        break;
                    }
                    switch ($value['$wildcard']) {
                        case ConfigProvider::WILDCARD_INFIX:
                            $compiled[] = "$phrase | *$phrase*";
                            break;
                        case ConfigProvider::WILDCARD_PREFIX:
                            $compiled[] = "$phrase | *$phrase";
                            break;
                        case ConfigProvider::WILDCARD_SUFFIX:
                            $compiled[] = "$phrase | $phrase*";
                            break;
                        case ConfigProvider::WILDCARD_DISABLED:
                            if (strpos($phrase, ' ') === false) {
                                $compiled[] = $phrase;
                            }
                            break;
                    }
                    break;
            }
        }

        return implode(' ', $compiled);
    }

    private function escape(string $value): string
    {
        $pattern = '/(\+|&&|\|\||\/|!|\(|\)|\{|}|\[|]|\^|"|~|@|#|\*|\?|:|\\\)/';
        $replace = '\\\$1';
        $value   = preg_replace($pattern, $replace, $value);

        $strPattern = ['-'];
        $strReplace = $value === '-' ? ['-'] : ['\-'];
        $value      = str_replace($strPattern, $strReplace, $value);

        return $value;
    }

    //    /**
    //     * @param array    $arQuery
    //     * @param SphinxQL $select
    //     * @return string
    //     */
    //    protected function buildMatchQuery($arQuery, $select)
    //    {
    //        $query = '';
    //
    //        if (!is_array($arQuery) || !count($arQuery)) {
    //            return '*';
    //        }
    //
    //        $result = [];
    //        foreach ($arQuery as $key => $array) {
    //            if ($key == '$!like') {
    //                $result[] = '-' . $this->buildWhere($key, $array, $select);
    //            } else {
    //                $result[] = $this->buildWhere($key, $array, $select);
    //            }
    //        }
    //
    //        if (count($result)) {
    //            $query = '(' . implode(' ', $result) . ')';
    //        }
    //
    //        return $query;
    //    }
    //
    //    /**
    //     * @param string   $type
    //     * @param array    $array
    //     * @param SphinxQL $select
    //     * @return array|string
    //     */
    //    protected function buildWhere($type, $array, $select)
    //    {
    //        if (!is_array($array)) {
    //            $array = str_replace('/', '\/', $array);
    //            if (substr($array, 0, 1) == ' ') {
    //                return '(' . $select->escapeMatch($array) . ')';
    //            } else {
    //                if (strlen($select->escapeMatch($array)) <= 1) {
    //                    return '(' . $select->escapeMatch($array) . '*)';
    //                } else {
    //                    return '(*' . $select->escapeMatch($array) . '*)';
    //                }
    //            }
    //        }
    //
    //        foreach ($array as $key => $subArray) {
    //            if ($key == '$or') {
    //                $array[$key] = $this->buildWhere($type, $subArray, $select);
    //                if (is_array($array[$key])) {
    //                    $array = '(' . implode(' | ', $array[$key]) . ')';
    //                }
    //            } elseif ($key == '$and') {
    //                $array[$key] = $this->buildWhere($type, $subArray, $select);
    //                if (is_array($array[$key])) {
    //                    $array = '(' . implode(' ', $array[$key]) . ')';
    //                }
    //            } else {
    //                $array[$key] = $this->buildWhere($type, $subArray, $select);
    //            }
    //        }
    //
    //        return $array;
    //    }
}
