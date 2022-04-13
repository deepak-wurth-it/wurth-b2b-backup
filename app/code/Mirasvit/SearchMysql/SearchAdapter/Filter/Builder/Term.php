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
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;

class Term implements FilterInterface
{
    const CONDITION_OPERATOR_EQUALS     = '=';
    const CONDITION_OPERATOR_NOT_EQUALS = '!=';
    const CONDITION_OPERATOR_IN         = 'IN';
    const CONDITION_OPERATOR_NOT_IN     = 'NOT IN';

    private $conditionManager;

    public function __construct(
        ConditionManager $conditionManager
    ) {
        $this->conditionManager = $conditionManager;
    }

    public function buildFilter(RequestFilterInterface $filter, bool $isNegation): string
    {
        /** @var \Magento\Framework\Search\Request\Filter\Term $filter */
        return $this->conditionManager->generateCondition(
            $filter->getField(),
            $this->getConditionOperator($filter->getValue(), $isNegation),
            $filter->getValue()
        );
    }

    /**
     * @param string|array $value
     */
    private function getConditionOperator($value, bool $isNegation): string
    {
        if (is_array($value)) {
            $operator = $isNegation ? self::CONDITION_OPERATOR_NOT_IN : self::CONDITION_OPERATOR_IN;
        } else {
            $operator = $isNegation ? self::CONDITION_OPERATOR_NOT_EQUALS : self::CONDITION_OPERATOR_EQUALS;
        }

        return $operator;
    }
}
