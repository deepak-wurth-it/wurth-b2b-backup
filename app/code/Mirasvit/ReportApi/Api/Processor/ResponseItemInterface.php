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



namespace Mirasvit\ReportApi\Api\Processor;

interface ResponseItemInterface
{
    /**
     * @param string $key
     * @return string[]|string
     */
    public function getData($key = null);

    /**
     * @return ResponseItemInterface[]
     */
    public function getItems();

    /**
     * @param ResponseItemInterface[] $items
     * @return $this
     */
    public function setItems($items);

    /**
     * @param ResponseItemInterface $item
     * @return $this
     */
    public function addItem(ResponseItemInterface $item);

    /**
     * @param string $key
     * @return string[]|string
     */
    public function getFormattedData($key = null);

    /**
     * @return array
     */
    public function toArray();
}
