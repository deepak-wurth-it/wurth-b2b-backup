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
 * @package   mirasvit/module-report-api
 * @version   1.0.49
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportApi\Processor;

use Magento\Framework\Api\AbstractSimpleObject;
use Mirasvit\ReportApi\Api\ResponseInterface;

class Response extends AbstractSimpleObject implements ResponseInterface
{
    const ITEMS   = 'items';
    const REQUEST = 'request';
    const TOTALS  = 'totals';
    const SIZE    = 'size';
    const COLUMNS = 'columns';

    /**
     * @return array
     */
    public function toArray()
    {
        $columns = [];
        foreach ($this->getColumns() as $column) {
            $columns[] = $column->getName();
        }

        $items = [];
        foreach ($this->getItems() as $item) {
            $items[] = $item->toArray();
        }

        return [
            self::REQUEST => $this->getRequest()->toArray(),
            self::SIZE    => $this->getSize(),
            self::COLUMNS => $columns,
            self::TOTALS  => [
                ResponseItem::DATA           => $this->getTotals()->getData(),
                ResponseItem::FORMATTED_DATA => $this->getTotals()->getFormattedData(),
            ],
            self::ITEMS   => $items,
        ];
    }

    /**
     * @return \Mirasvit\ReportApi\Api\Processor\ResponseColumnInterface[]|mixed|null
     */
    public function getColumns()
    {
        return $this->_get(self::COLUMNS);
    }

    /**
     * @return \Mirasvit\ReportApi\Api\Processor\ResponseItemInterface[]|mixed|null
     */
    public function getItems()
    {
        return $this->_get(self::ITEMS);
    }

    /**
     * @return \Mirasvit\ReportApi\Api\RequestInterface|mixed|null
     */
    public function getRequest()
    {
        return $this->_get(self::REQUEST);
    }

    /**
     * @return int|mixed|null
     */
    public function getSize()
    {
        return $this->_get(self::SIZE);
    }

    /**
     * @return \Mirasvit\ReportApi\Api\Processor\ResponseItemInterface|mixed|null
     */
    public function getTotals()
    {
        return $this->_get(self::TOTALS);
    }
}
