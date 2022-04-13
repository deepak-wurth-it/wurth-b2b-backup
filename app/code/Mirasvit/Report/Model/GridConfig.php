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
 * @package   mirasvit/module-report
 * @version   1.3.112
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Report\Model;

use Magento\Framework\Api\AbstractSimpleObject;

class GridConfig extends AbstractSimpleObject
{
    const PAGINATION = 'pagination';
    const ORDER_COLUMN = 'order_column';
    const ORDER_DIRECTION = 'order_direction';

    public function __construct()
    {
        parent::__construct([
            self::PAGINATION => true,
        ]);
    }

    /**
     * @return $this
     */
    public function enablePagination()
    {
        return $this->setData(self::PAGINATION, true);
    }

    /**
     * @return $this
     */
    public function disablePagination()
    {
        return $this->setData(self::PAGINATION, false);
    }

    /**
     * @return bool
     */
    public function isPaginationActive()
    {
        return $this->_get(self::PAGINATION);
    }

    /**
     * @param string $column
     * @return $this
     */
    public function setOrderColumn($column)
    {
        return $this->setData(self::ORDER_COLUMN, $column);
    }

    /**
     * @return string|null
     */
    public function getOrderColumn()
    {
        return $this->_get(self::ORDER_COLUMN);
    }

    /**
     * @param string $direction
     * @return $this
     */
    public function setOrderDirection($direction)
    {
        return $this->setData(self::ORDER_DIRECTION, $direction);
    }

    /**
     * @return string|null
     */
    public function getOrderDirection()
    {
        return $this->_get(self::ORDER_DIRECTION);
    }
}