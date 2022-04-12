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



namespace Mirasvit\SearchMysql\SearchAdapter\Filter;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;
use Magento\Framework\Search\Request\Query\BoolExpression;

class Builder
{
    private $conditionManager;

    private $filters = [];

    private $preprocessor;

    public function __construct(
        Builder\Range $range,
        Builder\Term $term,
        Builder\Wildcard $wildcard,
        ConditionManager $conditionManager,
        Preprocessor $preprocessor
    ) {
        $this->filters          = [
            RequestFilterInterface::TYPE_RANGE    => $range,
            RequestFilterInterface::TYPE_TERM     => $term,
            RequestFilterInterface::TYPE_WILDCARD => $wildcard,
        ];
        $this->conditionManager = $conditionManager;
        $this->preprocessor     = $preprocessor;
    }

    public function build(RequestFilterInterface $filter, string $conditionType): string
    {
        return $this->processFilter($filter, $this->isNegation($conditionType));
    }

    private function processFilter(RequestFilterInterface $filter, bool $isNegation): string
    {
        if ($filter->getType() === RequestFilterInterface::TYPE_BOOL) {
            $query = $this->processBoolFilter($filter, $isNegation);
            $query = $this->conditionManager->wrapBrackets($query);
        } else {
            if (!isset($this->filters[$filter->getType()])) {
                throw new \InvalidArgumentException('Unknown filter type ' . $filter->getType());
            }
            $query = $this->filters[$filter->getType()]->buildFilter($filter, $isNegation);
            $query = $this->preprocessor->process($filter, $isNegation, $query);
        }

        return $query;
    }

    private function processBoolFilter(RequestFilterInterface $filter, bool $isNegation): string
    {
        $must    = $this->buildFilters($filter->getMust(), Select::SQL_AND, $isNegation);
        $should  = $this->buildFilters($filter->getShould(), Select::SQL_OR, $isNegation);
        $mustNot = $this->buildFilters(
            $filter->getMustNot(),
            Select::SQL_AND,
            !$isNegation
        );

        $queries = [
            $must,
            $this->conditionManager->wrapBrackets($should),
            $this->conditionManager->wrapBrackets($mustNot),
        ];

        return $this->conditionManager->combineQueries($queries, Select::SQL_AND);
    }

    private function buildFilters(array $filters, string $unionOperator, bool $isNegation): string
    {
        $queries = [];
        foreach ($filters as $filter) {
            $filterQuery = $this->processFilter($filter, $isNegation);
            $queries[]   = $this->conditionManager->wrapBrackets($filterQuery);
        }

        return $this->conditionManager->combineQueries($queries, $unionOperator);
    }

    private function isNegation(string $conditionType): bool
    {
        return BoolExpression::QUERY_CONDITION_NOT === $conditionType;
    }
}
