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
 * @package   mirasvit/module-search-ultimate
 * @version   2.0.56
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\SearchReport\Ui\QuickDataBar;

use Magento\Backend\Block\Template;
use Mirasvit\Core\Ui\QuickDataBar\SparklineDataBlock;
use Mirasvit\SearchReport\Api\Data\LogInterface;

class SessionsDataBlock extends SparklineDataBlock
{
    private $dataProvider;

    public function __construct(
        DataProvider     $dataProvider,
        Template\Context $context
    ) {
        $this->dataProvider = $dataProvider;

        parent::__construct($context);
    }

    public function getLabel(): string
    {
        return (string)__('Sessions with search');
    }

    public function getScalarValue(): string
    {
        $value = $this->dataProvider->getScalarValue(
            new \Zend_Db_Expr('COUNT(DISTINCT(session))'),
            new \Zend_Db_Expr('1'),
            $this->dateFrom,
            $this->dateTo
        );

        return $this->dataProvider->number($value);
    }

    public function getSparklineValues(): array
    {
        $dateExpr = $this->getDateIntervalExpr(LogInterface::CREATED_AT);

        return $this->dataProvider->getSparklineValues(
            new \Zend_Db_Expr('COUNT(DISTINCT(session))'),
            new \Zend_Db_Expr('1'),
            $dateExpr,
            $this->dateFrom,
            $this->dateTo
        );
    }
}
