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


namespace Mirasvit\SearchMysql\SearchAdapter\Filter\Builder;

use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Search\Request\Filter\Range as RangeFilterRequest;
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;

class Range implements FilterInterface
{
    const CONDITION_PART_GREATER_THAN = '>=';
    const CONDITION_PART_LOWER_THAN = '<=';
    const CONDITION_NEGATION_PART_GREATER_THAN = '>';
    const CONDITION_NEGATION_PART_LOWER_THAN = '<';

    private $conditionManager;

    public function __construct(
        ConditionManager $conditionManager
    ) {
        $this->conditionManager = $conditionManager;
    }

    public function buildFilter(RequestFilterInterface $filter, bool $isNegation): string
    {
        /** @var RangeFilterRequest $filter */
        $queries = [
            $this->getLeftConditionPart($filter, $isNegation),
        ];

        if ($filter->getTo() !== null) {
            $queries[] = $this->getRightConditionPart($filter, $isNegation);
        }

        $unionOperator = $this->getConditionUnionOperator($isNegation);

        return $this->conditionManager->combineQueries($queries, $unionOperator);
    }

    private function getLeftConditionPart(RequestFilterInterface $filter, bool $isNegation): string
    {
        return $this->getPart(
            $filter->getField(),
            ($isNegation ? self::CONDITION_NEGATION_PART_LOWER_THAN : self::CONDITION_PART_GREATER_THAN),
            (string) $filter->getFrom()
        );
    }

    private function getRightConditionPart(RequestFilterInterface $filter, bool $isNegation): string
    {
        return $this->getPart(
            $filter->getField(),
            ($isNegation ? self::CONDITION_NEGATION_PART_GREATER_THAN : self::CONDITION_PART_LOWER_THAN),
            $filter->getTo()
        );
    }

    private function getPart(string $field, string $operator, string $value): string
    {
        return $value === null
            ? ''
            : $this->conditionManager->generateCondition($field, $operator, $value);
    }

    private function getConditionUnionOperator(bool $isNegation): string
    {
        return $isNegation ? \Magento\Framework\DB\Select::SQL_OR : \Magento\Framework\DB\Select::SQL_AND;
    }
}
