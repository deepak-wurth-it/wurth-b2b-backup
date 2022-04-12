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

namespace Mirasvit\SearchReport\Ui\QuickDataBar;

use Magento\Framework\App\ResourceConnection;
use Mirasvit\SearchReport\Api\Data\LogInterface;

class DataProvider
{
    private $resource;

    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    public function getScalarValue(\Zend_Db_Expr $column, \Zend_Db_Expr $where, \DateTime $from, \DateTime $to): float
    {
        $columns = [
            'value' => $column,
        ];

        $select = $this->resource->getConnection()
            ->select()
            ->from($this->resource->getTableName(LogInterface::TABLE_NAME), $columns)
            ->where((string)$where)
            ->where('source="catalogsearch_result_index"')
            ->where(LogInterface::CREATED_AT . ' >= ?', $from)
            ->where(LogInterface::CREATED_AT . ' <= ?', $to);

        $value = $this->resource->getConnection()->fetchOne($select);

        return (float)$value;
    }

    public function getSparklineValues(\Zend_Db_Expr $column, \Zend_Db_Expr $where, \Zend_Db_Expr $dateExpr, \DateTime $from, \DateTime $to): array
    {
        $columns = [
            'date'  => $dateExpr,
            'value' => $column,
        ];

        $select = $this->resource->getConnection()
            ->select()
            ->from($this->resource->getTableName(LogInterface::TABLE_NAME), $columns)
            ->where((string)$where)
            ->where('source="catalogsearch_result_index"')
            ->where(LogInterface::CREATED_AT . ' >= ?', $from)
            ->where(LogInterface::CREATED_AT . ' <= ?', $to)
            ->order(LogInterface::CREATED_AT)
            ->group($dateExpr);

        $result = [];
        foreach ($this->resource->getConnection()->fetchPairs($select) as $date => $value) {
            $result[$date] = (int)$value;
        }

        return $result;
    }

    public function number(float $value): string
    {
        return number_format($value, 0, '.', ' ');
    }
}
