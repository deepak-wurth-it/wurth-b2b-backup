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
 * @package   mirasvit/module-core
 * @version   1.3.3
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Core\Ui\QuickDataBar;

abstract class SparklineDataBlock extends ScalarDataBlock
{
    protected $_template = 'Mirasvit_Core::quickDataBar/sparklineData.phtml';

    public abstract function getSparklineValues(): array;

    public function toArray(array $keys = []): array
    {
        return array_merge(parent::toArray(), [
            'sparkline' => $this->fillGaps($this->getSparklineValues()),
        ]);
    }

    public function getDateIntervalExpr(string $column): \Zend_Db_Expr
    {
        $interval = $this->dateTo->getTimestamp() - $this->dateFrom->getTimestamp();

        if ($interval > 90 * 24 * 60 * 60) {
            $groupSql = 'DATE_FORMAT(' . $column . ', "%Y-%m")';
        } elseif ($interval > 24 * 60 * 60) {
            $groupSql = 'DATE_FORMAT(' . $column . ', "%Y-%m-%d")';
        } else {
            $groupSql = 'DATE_FORMAT(' . $column . ', "%Y-%m-%d-%h")';
        }

        return new \Zend_Db_Expr($groupSql);
    }

    private function fillGaps(array $values): array
    {
        $interval = $this->dateTo->getTimestamp() - $this->dateFrom->getTimestamp();

        $ts = clone $this->dateFrom;
        if ($interval > 90 * 24 * 60 * 60) {
            $dateInterval = new \DateInterval('P1M');
            $dateFormat   = 'Y-m';
            $ts->setTime(0, 0, 0);
        } elseif ($interval > 24 * 60 * 60) {
            $dateInterval = new \DateInterval('P1D');
            $dateFormat   = 'Y-m-d';
            $ts->setTime(0, 0, 0);
        } else {
            $dateInterval = new \DateInterval('PT1H');
            $dateFormat   = 'Y-m-d-h';
        }

        $template = [];

        while ($ts->getTimestamp() <= $this->dateTo->getTimestamp()) {
            $template[$ts->format($dateFormat)] = 0;

            $ts = $ts->add($dateInterval);
        }

        foreach ($values as $k => $v) {
            $template[$k] = $v;
        }

        $result = [];

        foreach ($template as $k => $v) {
            $result[date('d M, Y', strtotime($k))] = $v;
        }

        return $result;
    }
}
