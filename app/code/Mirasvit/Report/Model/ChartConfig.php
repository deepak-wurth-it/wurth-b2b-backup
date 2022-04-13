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

class ChartConfig extends AbstractSimpleObject
{
    const TYPE = 'type';
    const DEFAULT_COLUMNS = 'default_columns';

    public function __construct()
    {
        parent::__construct([
            self::TYPE            => false,
            self::DEFAULT_COLUMNS => [],
        ]);
    }

    /**
     * @param string|bool $type
     * @return $this
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * @return string|false
     */
    public function getType()
    {
        return $this->_get(self::TYPE);
    }

    /**
     * @param  array $columns
     * @return $this
     */
    public function setDefaultColumns(array $columns)
    {
        return $this->setData(self::DEFAULT_COLUMNS, $columns);
    }

    /**
     * @return string
     */
    public function getDefaultColumns()
    {
        return $this->_get(self::DEFAULT_COLUMNS);
    }
}
