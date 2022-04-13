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



namespace Mirasvit\Report\Ui;

use Magento\Backend\Block\Template;
use Magento\Framework\Profiler;
use Magento\Framework\Registry;
use Mirasvit\Report\Api\Data\ReportInterface;
use Mirasvit\Report\Api\Repository\ReportRepositoryInterface;
use Mirasvit\Report\Service\StateService;
use Mirasvit\ReportApi\Api\Service\ColumnServiceInterface;

class ReportDataProvider extends Template
{
    /**
     * @var ReportRepositoryInterface
     */
    private $reportRepository;

    /**
     * @var ColumnServiceInterface
     */
    private $columnService;

    /**
     * @var StateService
     */
    private $stateService;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * ReportDataProvider constructor.
     *
     * @param ReportRepositoryInterface $reportRepository
     * @param ColumnServiceInterface    $columnService
     * @param StateService              $stateService
     * @param Registry                  $registry
     * @param Template\Context          $context
     */
    public function __construct(
        ReportRepositoryInterface $reportRepository,
        ColumnServiceInterface $columnService,
        StateService $stateService,
        Registry $registry,
        Template\Context $context
    ) {
        $this->reportRepository = $reportRepository;
        $this->columnService    = $columnService;
        $this->stateService     = $stateService;
        $this->registry         = $registry;
        $this->urlBuilder       = $context->getUrlBuilder();

        parent::__construct($context);
    }

    /**
     * @return array|null
     */
    public function getConfigData()
    {
        Profiler::start(__METHOD__);

        $currentReport = $this->getReport();

        if (!$currentReport) {
            return null;
        }

        $result = [
            'report'     => $currentReport->getIdentifier(),
            'reports'    => [],
            'requestUrl' => $this->getApiRequestUrl(),
            'stateUrl'   => $this->getApiStateUrl(),
            'exportUrl'  => $this->getApiExportUrl(),
        ];

        foreach ($this->reportRepository->getList() as $report) {
            if ($report->getIdentifier() !== $currentReport->getIdentifier()) {
                continue;
            }

            $report->init();

            $applicableColumns = $this->columnService->getApplicableColumns($report->getDimensions());

            $state = [
                'identifier'   => $report->getIdentifier(),
                'table'        => $report->getTable(),
                'dimensions'   => $report->getDimensions(),
                'columns'      => $report->getColumns(),
                'filters'      => $report->getFilters(),
                'sortOrders'   => [],
                'currentPage'  => 1,
                'pageSize'     => 20,
                'chartType'    => $report->getChartConfig()->getType(),
                'chartColumns' => $report->getChartConfig()->getDefaultColumns(),
            ];

            $state = $this->stateService->mergeState($report->getIdentifier(), $state);

            $schema = [
                'primaryFilters'       => $report->getPrimaryFilters(),
                'primaryDimensions'    => $report->getPrimaryDimensions(),
                'applicableDimensions' => $this->columnService->getApplicableDimensions($report->getPrimaryDimensions()),
                'applicableColumns'    => $applicableColumns,
                'internalColumns'      => $report->getInternalColumns(),
                'internalFilters'      => $report->getInternalFilters(),
            ];

            if (method_exists($report, 'getApplicableColumns')) {
                $schema['applicableColumns'] = $report->getApplicableColumns();
            }

            if (method_exists($report, 'getApplicableDimensions')) {
                $schema['applicableDimensions'] = $report->getApplicableDimensions();
            }

            $result['reports'][$report->getIdentifier()] = [
                'identifier' => $report->getIdentifier(),
                'name'       => $report->getName(),
                'state'      => $state,
                'schema'     => $schema,
            ];
        }

        Profiler::stop(__METHOD__);

        return $result;
    }

    /**
     * @return string
     */
    public function getApiRequestUrl()
    {
        return $this->urlBuilder->getUrl('report/api/request');
    }

    /**
     * @return string
     */
    public function getApiStateUrl()
    {
        return $this->urlBuilder->getUrl('report/api/state');
    }

    /**
     * @return string
     */
    public function getApiExportUrl()
    {
        return $this->urlBuilder->getUrl('report/api/export');
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        try {
            $json = \Zend_Json::encode($this->getConfigData());
        } catch (\Exception $e) {
            return "<div class='message message-error'>" . $e->getMessage() . "</div>";
        }

        return "<script>var reportDataProvider = $json</script>";
    }

    /**
     * @return ReportInterface
     */
    private function getReport()
    {
        return $this->registry->registry('current_report');
    }
}
