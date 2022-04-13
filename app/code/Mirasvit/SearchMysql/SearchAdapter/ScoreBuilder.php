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

namespace Mirasvit\SearchMysql\SearchAdapter;

class ScoreBuilder
{
    const WEIGHT_FIELD = 'search_weight';

    private $scorePool = [];

    private $queryPool = [];

    public function build(): \Zend_Db_Expr
    {
        $scoreCondition = $this->summarize($this->scorePool);
        $scoreCondition = $scoreCondition ? $scoreCondition : "0";
        $this->clear();
        $scoreAlias = $this->getScoreAlias();

        return new \Zend_Db_Expr("({$scoreCondition}) AS {$scoreAlias}");
    }

    public function getScoreAlias(): string
    {
        return 'score';
    }

    public function startQuery(): void
    {
        $this->queryPool = [];
    }

    public function endQuery(int $boost): void
    {
        $condition = $this->summarize($this->queryPool);

        if (!$condition) {
            return;
        }

        $this->scorePool[] = "{$condition} * {$boost}";
    }

    public function addCondition(\Zend_Db_Expr $condition, bool $useWeights = true): void
    {
        $condition = "{$condition}";

        if (!$condition) {
            return;
        }

        $weight = 'cea.' . self::WEIGHT_FIELD;

        if ($useWeights) {
            $condition = "SUM({$condition} * POW(2, $weight))";
        }

        $this->queryPool[] = $condition;
    }

    private function summarize(array $conditions): string
    {
        if (count($conditions) === 0) {
            return '';
        }

        if (count($conditions) === 1) {
            return $conditions[0];
        }

        return 'SUM(' . implode(', ', $conditions) . ')';
    }

    private function clear(): void
    {
        $this->scorePool = [];
    }
}
