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



namespace Mirasvit\ReportApi\Api\Config;

interface TypeInterface
{
    const VALUE_TYPE_STRING = 'string';
    const VALUE_TYPE_NUMBER = 'number';

    const TYPE_COUNTRY = 'country';
    const TYPE_DATE    = 'date';
    const TYPE_FK      = 'fk';
    const TYPE_PK      = 'pk';
    const TYPE_MONEY   = 'money';
    const TYPE_QTY     = 'qty';
    const TYPE_NUMBER  = 'number';
    const TYPE_SELECT  = 'select';
    const TYPE_PERCENT = 'percent';
    const TYPE_STORE   = 'store';
    const TYPE_STR     = 'str';

    const JS_TYPE_TEXT    = 'text';
    const JS_TYPE_HTML    = 'html';
    const JS_TYPE_PERCENT = 'percent';
    const JS_TYPE_SELECT  = 'select';
    const JS_TYPE_NUMBER  = 'number';
    const JS_TYPE_MONEY   = 'money';

    const FILTER_TYPE_TEXT       = 'text';
    const FILTER_TYPE_SELECT     = 'select';
    const FILTER_TYPE_TEXT_RANGE = 'textRange';
    const FILTER_TYPE_DATE_RANGE = 'dateRange';

    const NA = 'N/A';

    /**
     * @return string
     */
    public function getType();

    /**
     * @return string[]
     */
    public function getAggregators();

    /**
     * @return string
     */
    public function getValueType();

    /**
     * @return string
     */
    public function getJsType();

    /**
     * @return string
     */
    public function getJsFilterType();

    /**
     * @param string|number       $actualValue
     * @param AggregatorInterface $aggregator
     * @return string|number
     */
    public function getFormattedValue($actualValue, AggregatorInterface $aggregator);

    /**
     * @param string|number       $actualValue
     * @param AggregatorInterface $aggregator
     * @return string
     */
    public function getPk($actualValue, AggregatorInterface $aggregator);
}
