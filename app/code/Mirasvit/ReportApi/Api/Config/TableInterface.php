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

interface TableInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getGroup();

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @return bool
     */
    public function isNative();

    /**
     * @return ColumnInterface[]
     */
    public function getColumns();

    /**
     * @param string $name
     *
     * @return ColumnInterface
     */
    public function getColumn($name);

    /**
     * @return FieldInterface
     */
    public function getPkField();

    /**
     * @param ColumnInterface $column
     *
     * @return $this
     */
    public function addColumn(ColumnInterface $column);

    /**
     * @param string $name
     *
     * @return FieldInterface
     */
    public function getField($name);

    /**
     * @return FieldInterface[]
     */
    public function getFields();

    /**
     * @return string
     */
    public function getConnectionName();

    /**
     * Whether a table is temporary or not.
     * @return bool
     */
    public function isTmp();

    /**
     * Set table as temporary
     *
     * @param bool|true $isTmp
     *
     * @return $this
     */
    public function setIsTmp($isTmp = true);
}
