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



namespace Mirasvit\ReportApi\Api\Service;

use Mirasvit\ReportApi\Api\Config\ColumnInterface;
use Mirasvit\ReportApi\Api\Config\TableInterface;
use Mirasvit\ReportApi\Api\RequestInterface;
use Mirasvit\ReportApi\Handler\Select;

interface SelectPillInterface
{
    /**
     * Is pill applicable to current request.
     * @param RequestInterface $request
     * @param ColumnInterface $column
     * @param TableInterface $table
     * @return bool
     */
    public function isApplicable(RequestInterface $request, ColumnInterface $column, TableInterface $table);

    /**
     * Apply fix to select.
     * @param Select           $select
     * @param ColumnInterface  $column
     * @param TableInterface   $baseTable
     * @param RequestInterface $request
     * @return void
     */
    public function take(
        Select $select,
        ColumnInterface $column,
        TableInterface $baseTable,
        RequestInterface $request
    );
}
