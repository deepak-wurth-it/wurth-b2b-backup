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



namespace Mirasvit\Report\Repository\Email;

use Mirasvit\Report\Api\Repository\Email\BlockRepositoryInterface;
use Mirasvit\Report\Api\Repository\ReportRepositoryInterface;
use Mirasvit\Report\Api\Service\DateServiceInterface;
use Mirasvit\Report\Service\EmailService;
use Mirasvit\ReportApi\Api\Processor\ResponseColumnInterface;
use Mirasvit\ReportApi\Api\Processor\ResponseItemInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DefaultRepository implements BlockRepositoryInterface
{
    /**
     * @var ReportRepositoryInterface
     */
    protected $reportRepository;

    /**
     * @var DateServiceInterface
     */
    protected $dateService;

    /**
     * @var EmailService
     */
    private $emailService;

    public function __construct(
        ReportRepositoryInterface $reportRepository,
        DateServiceInterface $dateService,
        EmailService $emailService
    ) {
        $this->reportRepository = $reportRepository;
        $this->dateService      = $dateService;
        $this->emailService     = $emailService;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlocks()
    {
        $blocks = [];
        foreach ($this->reportRepository->getList() as $report) {
            if ($report->getName()) {
                $blocks[$report->getIdentifier()] = __('Report: %1', $report->getName());
            }
        }

        return $blocks;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getContent($identifier, $data)
    {
        return $this->build($data);
    }

    /**
     * @param array $reportData
     *
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function build(array $reportData)
    {
        $response = $this->emailService->buildReportResponse($reportData);

        $rows = [];
        foreach ($response->getColumns() as $column) {
            $rows['header'][] = $column->getLabel();
        }

        foreach ($response->getItems() as $item) {
            $this->addRow($rows, $item, $response->getColumns());
        }

        foreach ($response->getTotals()->getFormattedData() as $key => $value) {
            $rows['footer'][] = $value;
        }

        $table = '<table>';
        foreach ($rows as $idx => $row) {
            $table .= '<tr>';
            foreach ($row as $column) {
                if ($idx === 'header' || $idx === 'footer') {
                    $table .= '<th>' . $column . '</th>';
                } else {
                    $table .= '<td>' . $column . '</td>';
                }
            }
            $table .= '</tr>';
        }

        $table .= '</table>';

        $report = $this->reportRepository->get($reportData['identifier']);
        $name   = $report->getName();

        return "
            <h2>{$name}</h2>
            <div class='interval'>{$this->dateService->getIntervalHint($reportData['timeRange'])}</div>
            
            <div class='table-wrapper'>$table</div>
        ";
    }

    /**
     * @param array                 $rows
     * @param ResponseItemInterface $item
     * @param array                 $columns
     */
    private function addRow(&$rows, ResponseItemInterface $item, array $columns)
    {
        $formattedData = $item->getFormattedData();

        $data = [];
        /** @var ResponseColumnInterface $column */
        foreach ($columns as $column) {
            $name = $column->getName();

            if (isset($formattedData[$name])) {
                $data[] = $formattedData[$name];
            } else {
                $data[] = '';
            }
        }

        $rows[] = $data;

        foreach ($item->getItems() as $subItem) {
            $this->addRow($rows, $subItem, $columns);
        }
    }
}
