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



namespace Mirasvit\ReportApi\Api;

interface ResponseInterface
{
    /**
     * @return \Mirasvit\ReportApi\Api\Processor\ResponseColumnInterface[]
     */
    public function getColumns();

    /**
     * @return int
     */
    public function getSize();

    /**
     * @return \Mirasvit\ReportApi\Api\Processor\ResponseItemInterface
     */
    public function getTotals();

    /**
     * @return \Mirasvit\ReportApi\Api\Processor\ResponseItemInterface[]
     */
    public function getItems();

    /**
     * @return \Mirasvit\ReportApi\Api\RequestInterface
     */
    public function getRequest();

    /**
     * @return array
     */
    public function toArray();
}
