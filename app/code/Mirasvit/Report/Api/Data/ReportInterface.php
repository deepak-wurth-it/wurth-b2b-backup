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



namespace Mirasvit\Report\Api\Data;

use Mirasvit\Report\Model\ChartConfig;
use Mirasvit\Report\Model\GridConfig;

interface ReportInterface
{
    const TABLE = 'table';

    const COLUMNS            = 'columns';
    const DIMENSIONS         = 'dimensions';
    const INTERNAL_COLUMNS   = 'internal_columns';
    const INTERNAL_FILTERS   = 'internal_filters';
    const FILTERS            = 'filters';
    const PRIMARY_FILTERS    = 'primary_filters';
    const PRIMARY_DIMENSIONS = 'primary_dimensions';

    const GRID_CONFIG  = 'grid_config';
    const CHART_CONFIG = 'chart_config';

    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return $this
     */
    public function init();

    /**
     * @return string
     */
    public function getTable();

    /**
     * @param string $tableName
     *
     * @return $this
     */
    public function setTable($tableName);

    /** STATE */

    /**
     * @return string[]
     */
    public function getColumns();

    /**
     * @param string[] $columns
     *
     * @return $this
     */
    public function setColumns(array $columns);

    /**
     * @return string[]
     */
    public function getDimensions();

    /**
     * @param string[] $columns
     *
     * @return $this
     */
    public function setDimensions(array $columns);

    /**
     * @return string[]
     */
    public function getInternalColumns();

    /**
     * @param string[] $columns
     *
     * @return $this
     */
    public function setInternalColumns(array $columns);

    /**
     * @return array
     */
    public function getInternalFilters();

    /**
     * @param string[] $filters
     *
     * @return $this
     */
    public function setInternalFilters(array $filters);

    /** SCHEMA */

    /**
     * @return string[]
     */
    public function getPrimaryDimensions();

    /**
     * @param string[] $columns
     *
     * @return $this
     */
    public function setPrimaryDimensions(array $columns);

    /**
     * @return array
     */
    public function getFilters();

    /**
     * @param string[] $filters
     *
     * @return $this
     */
    public function setFilters(array $filters);

    /**
     * @return string[]
     */
    public function getPrimaryFilters();

    /**
     * @param string[] $columns
     *
     * @return $this
     */
    public function setPrimaryFilters(array $columns);


    /**
     * @return GridConfig
     */
    public function getGridConfig();

    /**
     * @return ChartConfig
     */
    public function getChartConfig();
}
