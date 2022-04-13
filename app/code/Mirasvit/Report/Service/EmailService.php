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

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Csv;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Mirasvit\Report\Api\Data\EmailInterface;
use Mirasvit\Report\Api\Repository\Email\BlockRepositoryInterface;
use Mirasvit\Report\Api\Repository\EmailRepositoryInterface;
use Mirasvit\Report\Api\Repository\ReportRepositoryInterface;
use Mirasvit\Report\Api\Service\DateServiceInterface;
use Mirasvit\Report\Api\Service\EmailServiceInterface;
use Mirasvit\Report\Model\Mail\Template\TransportBuilder;
use Mirasvit\ReportApi\Api\Processor\ResponseItemInterface;
use Mirasvit\ReportApi\Api\RequestBuilderInterface;

class EmailService implements EmailServiceInterface
{

    const TYPE_OCTETSTREAM       = 'application/octet-stream';
    const DISPOSITION_ATTACHMENT = 'attachment';
    const ENCODING_BASE64        = 'base64';

    /**
     * @var Csv
     */
    protected $csvProcessor;

    /**
     * @var ReportRepositoryInterface
     */
    protected $reportRepository;

    /**
     * @var DateServiceInterface
     */
    protected $dateService;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var EmailRepositoryInterface
     */
    private $emailRepository;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var RequestBuilderInterface
     */
    private $requestBuilder;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    public function __construct(
        TransportBuilder $transportBuilder,
        EmailRepositoryInterface $emailRepository,
        ScopeConfigInterface $scopeConfig,
        TimezoneInterface $timezone,
        Csv $csvProcessor,
        DirectoryList $directoryList,
        RequestBuilderInterface $requestBuilder,
        ReportRepositoryInterface $reportRepository,
        DateServiceInterface $dateService
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->emailRepository  = $emailRepository;
        $this->scopeConfig      = $scopeConfig;
        $this->timezone         = $timezone;
        $this->csvProcessor     = $csvProcessor;
        $this->directoryList    = $directoryList;
        $this->requestBuilder   = $requestBuilder;
        $this->reportRepository = $reportRepository;
        $this->dateService      = $dateService;
    }

    /**
     * {@inheritdoc}
     */
    public function send(EmailInterface $email)
    {
        $vars = [
            'subject' => $email->getSubject() . ' [' . $this->timezone->date()->format("M d, Y H:i") . ']',
            'blocks'  => "",
        ];

        $definedReports = $this->emailRepository->getReports();
        $blocks         = $email->getBlocks();

        if (null === $blocks) {
            $blocks = [];
        }

        foreach ($blocks as $data) {
            if (isset($data['identifier'])) {
                $identifier = $data['identifier'];

                foreach ($definedReports as $report) {
                    if ((string)$report['value'] == (string)$identifier) {
                        /** @var BlockRepositoryInterface $repo */
                        $repo = $report['repository'];

                        if (!isset($data['timeRange'])) {
                            $data['timeRange'] = 'today';
                        }

                        $content = $repo->getContent($identifier, $data);

                        if ($email->getIsAttachEnabled() && (strpos($content, 'table') !== false) && $this->reportRepository->get($identifier) !== null) {
                            $preparedReport     = $this->prepareCsvReport($data);
                            $pathToCsvReports[] = $this->saveReportAsCsv($preparedReport, $identifier);
                        }

                        if ($content) {
                            $vars['blocks'] .= '<div class="block-wrapper">' . $content . '</div>';
                        }
                    }
                }
            }
        }

        $emails = explode(',', $email->getRecipient());

        $i = 1;
        foreach ($emails as $mail) {
            if (!trim($mail)) {
                continue;
            }

            $lastMailIdx = count($emails);
            /** @var TransportBuilder $transport */
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('report_email')
                ->setTemplateOptions([
                    'area'  => FrontNameResolver::AREA_CODE,
                    'store' => 0,
                ])
                ->setTemplateVars($vars)
                ->setFrom([
                    'name'  => $this->scopeConfig->getValue('trans_email/ident_general/name'),
                    'email' => $this->scopeConfig->getValue('trans_email/ident_general/email'),
                ])
                ->addTo($mail);

            if (!empty($pathToCsvReports)) {
                foreach ($pathToCsvReports as $pathToCsvReport) {
                    $filename    = explode('.', basename($pathToCsvReport));
                    $filename[0] .= '_' . $i;
                    $filename    = implode('.', $filename);

                    $transport->addAttachment(
                        file_get_contents($pathToCsvReport),
                        $mimeType = self::TYPE_OCTETSTREAM,
                        $disposition = self::DISPOSITION_ATTACHMENT,
                        $encoding = self::ENCODING_BASE64,
                        $filename
                    );
                }
            }

            $transport->getTransport()->sendMessage();

            if ($i == $lastMailIdx) {
                if (!empty($pathToCsvReports)) {
                    foreach ($pathToCsvReports as $pathToCsvReport) {
                        if (file_exists($pathToCsvReport)) {
                            unlink($pathToCsvReport);
                        }
                    }
                }
            }

            $i++;
        }

        $email->setLastSentAt(date('Y-m-d H:i:s', time()));
        $email->save();

    }

    /**
     * @param array $reportData
     *
     * @return array
     */
    public function prepareCsvReport($reportData)
    {
        $response = $this->buildReportResponse($reportData);

        $rows = [];
        foreach ($response->getColumns() as $column) {
            $rows['header'][] = $column->getLabel()->getText();
        }

        foreach ($response->getItems() as $item) {
            $this->addRow($rows, $item, $response->getColumns());
        }

        return $rows;
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
        /** @var \Mirasvit\ReportApi\Api\Processor\ResponseColumnInterface $column */
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

    /**
     * @param mixed  $preparedReport
     * @param string $identifier
     *
     * @return string
     */
    public function saveReportAsCsv($preparedReport, $identifier)
    {
        $fileName = date('j_m_Y') . '_report_' . $identifier . '.csv';
        $filePath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR)
            . "/" . $fileName;

        $this->csvProcessor
            ->setDelimiter(',')
            ->setEnclosure('"')
            ->saveData($filePath, $preparedReport);

        return $filePath;
    }

    /**
     * @param array $reportData
     *
     * @return \Mirasvit\ReportApi\Api\ResponseInterface
     */
    public function buildReportResponse($reportData)
    {
        $reportIdentifier = $reportData['identifier'];
        $report           = $this->reportRepository->get($reportIdentifier);
        $tableName        = $this->getTableName($report);
        $interval         = $this->dateService->getInterval($reportData['timeRange']);
        $request          = $this->requestBuilder->create()
            ->setTable($report->getTable())
            ->setDimensions($report->getDimensions())
            ->setPageSize((isset($reportData['limit']) && $reportData['limit']) ? $reportData['limit'] : 100000);

        $hasDateDimension = false;

        foreach ($report->getDimensions() as $column) {
            $request->addColumn($column);

            if (!$hasDateDimension && strpos($column, "_at") !== false) {
                $hasDateDimension = true;
            }
        }

        foreach ($report->getColumns() as $column) {
            $request->addColumn($column);
        }

        foreach ($report->getInternalFilters() as $filter) {
            if (strpos($filter['conditionType'], 'like') !== false) {
                $filter['value'] = '%' . $filter['value'] . '%';
            }

            if(!$hasDateDimension && strpos($filter['column'], "_at") !== false) {
                $hasDateDimension = true;
            }

            $request->addFilter($filter['column'], $filter['value'], $filter['conditionType']);
        }

        foreach($report->getPrimaryFilters() as $filter) {
            if(!$hasDateDimension && strpos($filter, "_at") !== false) {
                $hasDateDimension = true;
                $dimensionFilter  = $filter;
            }
        }

        $filterColumn = isset($dimensionFilter) ? $dimensionFilter : $tableName . '|created_at';

        //report can return not all data if original report doesn't have date dimension
        if ($hasDateDimension) {
            $request->addFilter($filterColumn, $interval->getFrom()->toString('Y-MM-dd HH:mm:ss'), 'gteq', 'A')
                ->addFilter($filterColumn, $interval->getTo()->toString('Y-MM-dd HH:mm:ss'), 'lteq', 'A');
        }

        return $request->process();
    }

    /**
     * @param \Mirasvit\ReportBuilder\Model\ReportInstance  $report
     * @return string
     */
    private function getTableName($report)
    {
        switch($report->getTable()) {
            case 'sales_shipment':
                return 'sales_shipment';
                break;

            case 'catalog_product_entity':
                return 'catalog_product_entity';
                break;

            default:
                return 'sales_order';
                break;
        }
    }
}
