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
use Mirasvit\ReportApi\Api\Processor\ResponseItemInterface;

class ResponseItem extends AbstractSimpleObject implements ResponseItemInterface
{
    const DATA           = 'data';
    const FORMATTED_DATA = 'formattedData';
    const ITEMS          = 'items';

    /**
     * @param string $key
     * @param mixed $value
     * @return $this|AbstractSimpleObject
     */
    public function setData($key, $value)
    {
        $this->_data[self::DATA][$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function setFormattedData($key, $value)
    {
        $this->_data[self::FORMATTED_DATA][$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function unsetData($key)
    {
        $this->_data[self::DATA][$key]           = null;
        $this->_data[self::FORMATTED_DATA][$key] = null;

        return $this;
    }

    /**
     * @param null $key
     * @return mixed|string|string[]|null
     */
    public function getData($key = null)
    {
        if ($key) {
            return isset($this->_get(self::DATA)[$key]) ? $this->_get(self::DATA)[$key] : null;
        }

        return $this->_get(self::DATA);
    }

    /**
     * @param null $key
     * @return mixed|string|string[]|null
     */
    public function getFormattedData($key = null)
    {
        if ($key) {
            return isset($this->_get(self::FORMATTED_DATA)[$key]) ? $this->_get(self::FORMATTED_DATA)[$key] : null;
        }

        return $this->_get(self::FORMATTED_DATA);
    }

    /**
     * @return array|ResponseItemInterface[]|mixed|null
     */
    public function getItems()
    {
        return $this->_get(self::ITEMS) ? $this->_get(self::ITEMS) : [];
    }

    /**
     * @param ResponseItemInterface[] $items
     * @return $this|ResponseItemInterface
     */
    public function setItems($items)
    {
        $this->_data[self::ITEMS] = $items;

        return $this;
    }

    /**
     * @param ResponseItemInterface $item
     * @return $this|ResponseItemInterface
     */
    public function addItem(ResponseItemInterface $item)
    {
        $items   = $this->getItems();
        $items[] = $item;
        $this->setItems($items);

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $items = [];

        foreach ($this->getItems() as $item) {
            $items[] = $item->toArray();
        }

        return [
            self::DATA           => $this->getData(),
            self::FORMATTED_DATA => $this->getFormattedData(),
            self::ITEMS          => $items,
        ];
    }
}
