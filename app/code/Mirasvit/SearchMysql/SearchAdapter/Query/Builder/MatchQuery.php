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



namespace Mirasvit\SearchMysql\SearchAdapter\Query\Builder;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Helper as DbHelper;
use Magento\Framework\DB\Helper\Mysql\Fulltext;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Mirasvit\Search\Model\ConfigProvider;
use Mirasvit\Search\Service\QueryService;
use Mirasvit\SearchMysql\SearchAdapter\Field\Field;
use Mirasvit\SearchMysql\SearchAdapter\Field\Resolver;
use Mirasvit\SearchMysql\SearchAdapter\ScoreBuilder;


class MatchQuery
{
    /**
     * List of special characters
     */
    const SPECIAL_CHARACTERS = '-+~/\\<>\'":*$#@()!,.?`=';

    protected $dbHelper;

    private   $replaceSymbols = [];

    private   $resolver;

    private   $fulltextHelper;

    private   $fulltextSearchMode;

    private   $queryService;

    private   $resource;

    public function __construct(
        ResourceConnection $resource,
        Resolver $resolver,
        Fulltext $fulltextHelper,
        DbHelper $dbHelper,
        QueryService $queryService,
        string $fulltextSearchMode = Fulltext::FULLTEXT_MODE_BOOLEAN
    ) {
        $this->resource           = $resource;
        $this->resolver           = $resolver;
        $this->replaceSymbols     = str_split(self::SPECIAL_CHARACTERS, 1);
        $this->fulltextHelper     = $fulltextHelper;
        $this->queryService       = $queryService;
        $this->dbHelper           = $dbHelper;
        $this->fulltextSearchMode = $fulltextSearchMode;

    }

    public function build(ScoreBuilder $scoreBuilder, Select $select, RequestQueryInterface $query, string $conditionType): Select
    {
        /** @var \Magento\Framework\Search\Request\Query\Match $query */
        $fieldList = [];
        foreach ($query->getMatches() as $match) {
            $fieldList[] = $match['field'];
        }

        $resolvedFieldList = $this->resolver->resolve($fieldList);

        $attributeCodes = [];
        $columns        = [];
        foreach ($resolvedFieldList as $field) {
            if ($field->getType() === Field::TYPE_FULLTEXT && $field->getAttributeId()) {
                $attributeCodes[] = $this->getAttributeCodeById($field->getAttributeId());
            }
            $column           = $field->getColumn();
            $columns[$column] = $column;
        }

        $searchQuery = $this->queryService->build($query->getValue());

        $exactMatchQuery = $this->compileQuery($columns, $searchQuery);

        $scoreQuery = $this->getScoreQuery($columns, $query->getValue());

        $scoreBuilder->startQuery();
        $scoreBuilder->addCondition(new \Zend_Db_Expr($scoreQuery), true);
        $scoreBuilder->endQuery(1);

        if ($attributeCodes) {
            $wrapped = [];
            foreach ($attributeCodes as $code) {
                if (!is_array($code)) {
                    $wrapped[] = "'{$code}'";
                }
            }
            $select->where(sprintf('search_index.attribute_code IN (%s)', implode(',', $wrapped)));
        }

        if ($exactMatchQuery) {
            $exactMatchQueryExpr = new \Zend_Db_Expr($exactMatchQuery);
            $select->having((string)$exactMatchQueryExpr);
        }

        $select->group('entity_id');

        return $select;
    }

    public function compileQuery(array $columns, array $query, bool $isNot = false): string
    {
        $compiled = [];
        foreach ($query as $directive => $value) {
            switch ($directive) {
                case '$like':
                    $compiled[] = '(' . $this->compileQuery($columns, $value, $isNot) . ')';
                    break;

                case '$!like':
                    $compiled[] = '(' . $this->compileQuery($columns, $value, true) . ')';
                    break;

                case '$and':
                    $and = [];
                    foreach ($value as $item) {
                        $and[] = $this->compileQuery($columns, $item, $isNot);
                    }
                    $compiled[] = '(' . implode(' and ', $and) . ')';
                    break;

                case '$or':
                    $or = [];
                    foreach ($value as $item) {
                        $or[] = $this->compileQuery($columns, $item, $isNot);
                    }
                    $compiled[] = '(' . implode(' or ', $or) . ')';
                    break;

                case '$term':
                    $phrase = $value['$phrase'];

                    switch ($value['$wildcard']) {
                        case ConfigProvider::WILDCARD_PREFIX:
                            $phrase = "$phrase ";
                            break;
                        case ConfigProvider::WILDCARD_SUFFIX:
                            $phrase = " $phrase";
                            break;
                        case ConfigProvider::WILDCARD_DISABLED:
                            $phrase = " $phrase ";
                            break;
                    }

                    $likes = [];
                    foreach ($columns as $attribute) {
                        $attribute = new \Zend_Db_Expr('GROUP_CONCAT(' . $attribute . ')');

                        $options = ['position' => 'any'];
                        if ($isNot) {
                            $likes[] = new \Zend_Db_Expr(
                                $attribute . ' NOT LIKE ' . $this->dbHelper->addLikeEscape($phrase, $options)
                            );
                        } else {
                            $likes[] = new \Zend_Db_Expr(
                                $attribute . ' LIKE ' . $this->dbHelper->addLikeEscape($phrase, $options)
                            );
                        }
                    }

                    $compiled[] = implode(' or ', $likes);

                    break;
            }
        }

        return implode(' AND ', $compiled);
    }

    public function getScoreQuery(array $columns, string $query): string
    {
        $cases     = [];
        $fullCases = [];

        $words = preg_split('#\s#siu', $query, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($columns as $column) {
            $cases[5][] = $this->dbHelper->getCILike($column, ' ' . $query . ' ');
        }

        foreach ($words as $word) {
            foreach ($columns as $column) {
                $cases[3][] = $this->dbHelper->getCILike($column, ' ' . $word . ' ', ['position' => 'any']);
                $cases[2][] = $this->dbHelper->getCILike($column, $word, ['position' => 'any']);
            }
        }

        foreach ($words as $word) {
            foreach ($columns as $column) {
                $e      = strlen($word) . ' * (';
                $e      .= '(' . strlen($word) . ' / LENGTH(' . $column . ')) + ';
                $e      .= '(1/(LENGTH(' . $column . ') - ( LENGTH(' . $column . ')
                    - LOCATE("' . $this->escape($word) . '",' . $column . ')))))';
                $locate = new \Zend_Db_Expr($e);

                $cases[$locate->__toString()][] = $locate;
            }
        }

        foreach ($cases as $weight => $conditions) {
            foreach ($conditions as $condition) {
                $fullCases[] = 'CASE WHEN ' . $condition . ' THEN ' . $weight . ' ELSE 0 END';
            }
        }

        if (count($fullCases)) {
            $select = '(' . implode('+', $fullCases) . ')';
        } else {
            $select = '0';
        }

        $select = 'CASE WHEN cea.search_weight > 1 OR search_index.attribute_code = "_misc" THEN(' . $select . ') ELSE 0 END';

        return $select;
    }

    protected function prepareFastQuery(string $queryValue, string $conditionType): string
    {
        $queryValue = str_replace($this->replaceSymbols, ' ', $queryValue);

        $stringPrefix = '';
        if ($conditionType === BoolExpression::QUERY_CONDITION_MUST) {
            $stringPrefix = '+';
        } elseif ($conditionType === BoolExpression::QUERY_CONDITION_NOT) {
            $stringPrefix = '-';
        }

        $queryValues = explode(' ', $queryValue);

        foreach ($queryValues as $queryKey => $queryValue) {
            if (empty($queryValue)) {
                unset($queryValues[$queryKey]);
            } else {
                $stringSuffix           = '*';
                $queryValues[$queryKey] = $stringPrefix . $queryValue . $stringSuffix;
            }
        }

        $queryValue = implode(' ', $queryValues);

        return $queryValue;
    }

    private function getAttributeCodeById(int $attributeId): array
    {
        $select = $this->resource->getConnection()->select()
            ->from($this->resource->getTableName('eav_attribute'), ['attribute_code'])
            ->where('attribute_id = ?', (int)$attributeId);

        return $select->getConnection()->fetchOne($select);
    }

    private function escape(string $value): string
    {
        $pattern = '/(\+|-|\/|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';
        $replace = '\\\$1';

        return preg_replace($pattern, $replace, $value);
    }
}
