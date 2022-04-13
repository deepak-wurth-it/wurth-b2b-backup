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



namespace Mirasvit\ReportApi\Config\Aggregator;

use Magento\Framework\App\ResourceConnection;
use Mirasvit\ReportApi\Api\Config\AggregatorInterface;

class Day implements AggregatorInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * Day constructor.
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return self::TYPE_DAY;
    }

    /**
     * @return array|\Zend_Db_Expr
     */
    public function getExpression()
    {
        return $this->resource->getConnection()->getDateFormatSql('%1', '%Y-%m-%d 00:00:00');
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Day';
    }
}
