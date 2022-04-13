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



namespace Mirasvit\Report\Service;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Variable\Model\Variable;
use Magento\Variable\Model\VariableFactory;
use Mirasvit\Report\Api\Data\ReportInterface;
use Mirasvit\Report\Api\Service\ColumnManagerInterface;

class ColumnManager implements ColumnManagerInterface
{
    const VAR_PREFIX = 'mst_report_';

    /**
     * @var array
     */
    protected $variables = [];

    /**
     * @var VariableFactory
     */
    private $variableFactory;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * ColumnManager constructor.
     * @param DateTime $dateTime
     * @param VariableFactory $variableFactory
     */
    public function __construct(
        DateTime $dateTime,
        VariableFactory $variableFactory
    ) {
        $this->variableFactory = $variableFactory;
        $this->dateTime        = $dateTime;
    }

    /**
     * Return stored columns, always add report's dimensions.
     * If no columns saved - return report's base columns.
     * {@inheritdoc}
     */
    public function getActiveColumns(ReportInterface $report)
    {
        if (!isset($this->variables[$report->getIdentifier()])) {
            /** @var Variable $var */
            $var = $this->variableFactory->create();
            $var->loadByCode(self::VAR_PREFIX . $report->getIdentifier());

            $columns = json_decode($var->getData('plain_value'), true);

            if (is_array($columns) && count($columns)) {
                // always display dimensions, fast filters and required columns
                $columns = array_unique(array_merge(
                    $columns,
                    $report->getDimensions(),
                    $report->getPrimaryFilters(),
                    $report->getInternalColumns()
                ));
            }

            $this->variables[$report->getIdentifier()] = $columns;
        }

        return $this->variables[$report->getIdentifier()] ? : $report->getColumns();
    }

    /**
     * {@inheritdoc}
     */
    public function setActiveColumns($report, array $columns = [])
    {
        /** @var Variable $var */
        $var = $this->variableFactory->create();
        $var->loadByCode(self::VAR_PREFIX . $report);

        $var->setData('plain_value', json_encode($columns))
            ->setData('html_value', $this->dateTime->gmtTimestamp())
            ->setName($report)
            ->setCode(self::VAR_PREFIX . $report)
            ->save();
    }
}
