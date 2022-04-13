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



namespace Mirasvit\Report\Console\Command;

use Magento\Framework\App\State;
use Magento\Framework\ObjectManagerInterface;
use Mirasvit\Report\Api\Repository\ReportRepositoryInterface;
use Mirasvit\ReportApi\Api\RequestBuilderInterface;
use Mirasvit\ReportApi\Api\ResponseInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    /**
     * @var ReportRepositoryInterface
     */
    private $reportRepository;

    /**
     * @var RequestBuilderInterface
     */
    private $requestBuilder;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var array
     */
    private $totals = [];

    /**
     * TestCommand constructor.
     * @param ReportRepositoryInterface $reportRepository
     * @param RequestBuilderInterface $requestBuilder
     * @param ObjectManagerInterface $objectManager
     * @param State $appState
     */
    public function __construct(
        ReportRepositoryInterface $reportRepository,
        RequestBuilderInterface $requestBuilder,
        ObjectManagerInterface $objectManager,
        State $appState
    ) {
        $this->reportRepository = $reportRepository;
        $this->requestBuilder   = $requestBuilder;
        $this->objectManager    = $objectManager;
        $this->appState         = $appState;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mirasvit:report:test')
            ->setDescription('For testing purpose')
            ->setDefinition([]);

        $this->addArgument('report', InputArgument::OPTIONAL);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->appState->setAreaCode('adminhtml');

        foreach ($this->reportRepository->getList() as $report) {
            if (!$report->getName()) {
                continue;
            }

            $output->writeln("<info>{$report->getIdentifier()}</info>");

            $report->init();

            $request = $this->requestBuilder->create();

            $request
                ->setTable($report->getTable())
                ->setDimensions($report->getDimensions())
                ->setColumns($report->getColumns())
                ->setPageSize(10);

            try {
                $ts = microtime(true);

                $request->process();

                $time = microtime(true) - $ts;

                $output->writeln($report->getName() . ' ' . $time);

                //                $this->renderResponse($output, $response, $time);

                //                $output->writeln($response->getRequest()->g());
            } catch (\Exception $e) {
                throw new \Exception($request, 0, $e);
            }

            //            foreach ($report->getDimensions() as $dimensionColumnName) {
            //                $output->writeln("<info>{$report->getIdentifier()} / $dimensionColumnName</info>");
            //
            //                $columns = array_merge_recursive([$dimensionColumnName], $report->getDefaultColumns());
            //                $this->processRequest($output, $report, $columns, $dimensionColumnName);
            //
            //                $columns = array_merge_recursive([$dimensionColumnName], $report->getColumns());
            //
            //                shuffle($columns);
            //                $chunks = array_chunk($columns, 30);
            //
            //                foreach ($chunks as $chunk) {
            //                    $this->processRequest($output, $report, $chunk, $dimensionColumnName);
            //                }
            //            }
        }
        //        $requests = [
        //            'a-1'     => [
        //                'table'      => 'catalog_product_entity',
        //                'dimensions' => [
        //                    'catalog_product_entity|sku',
        //                    'customer_group|customer_group_code'
        //                ],
        //                'columns'    => [
        //                    'catalog_product_entity|sku',
        //                    'customer_group|customer_group_code',
        //                    'catalog_product_entity_tier_price|value',
        //                ],
        //            ],
        //
        //            'a-1'     => [
        //                'table'      => 'sales_order',
        //                'dimensions' => [
        //                    'sales_order|created_at__month',
        //                ],
        //                'columns'    => [
        //                    'sales_order|created_at__month',
        //                    'sales_order|entity_id__cnt',
        //                    'sales_order|total_qty_ordered__sum',
        //                    'sales_order_item|qty_ordered__sum',
        //                    'sales_order|total_invoiced_cost__sum',
        //                ],
        //            ],
        //            'a-1.1'    => [
        //                'table'      => 'sales_order',
        //                'dimensions' => [
        //                    'sales_order|created_at__year',
        //                ],
        //                'columns'    => [
        //                    'sales_order|created_at__year',
        //                    'sales_order|entity_id__cnt',
        //                    'sales_order|total_qty_ordered__sum',
        //                    'sales_order_item|qty_ordered__sum',
        //                    'sales_order|total_invoiced_cost__sum',
        //                    'sales_order_item|cost__sum',
        //                ],
        //            ],
        //            'a-2'   => [
        //                'table'      => 'sales_order',
        //                'dimensions' => [
        //                    'sales_order|created_at__year',
        //                ],
        //                'columns'    => [
        //                    'sales_order_item|qty_ordered__sum',
        //                ],
        //            ],
        //            'a-2.1' => [
        //                'table'      => 'sales_order',
        //                'dimensions' => [
        //                    'catalog_product_entity|sku',
        //                ],
        //                'columns'    => [
        //                    'sales_order_item|qty_ordered__sum',
        //                ],
        //            ],
        //        ];

        //        foreach ($requests as $letter => $requestData) {
        //            $request = $this->requestBuilder->create();
        //
        //            $request
        //                ->setTable($requestData['table'])
        //                ->setDimensions($requestData['dimensions'])
        //                ->setColumns($requestData['columns']);
        //
        //            try {
        //                $ts = microtime(true);
        //
        //                $response = $request->process();
        //
        //                $time = microtime(true) - $ts;
        //                $this->renderResponse($output, $response, $time);
        //
        //                $output->writeln($response->getRequest()->getQuery());
        //            } catch (\Exception $e) {
        //                throw new \Exception($request, 0, $e);
        //            }
        //        }
        //
        //        return;
        /*
                $baseDimensions = [
                    'sales_order|created_at__day',
                    'catalog_product_entity|sku',
                ];

                $baseColumns = [
                    'sales_order|entity_id__cnt',
                    'sales_order|total_qty_ordered__sum',
                    'sales_order|discount_amount__sum',
                    'sales_order|shipping_amount__sum',
                    'sales_order|tax_amount__sum',
                    'sales_order|total_refunded__sum',
                    'sales_order|gross_margin__avg',
                    'sales_order|grand_total__sum',
                    'sales_order|entity_id__cnt',
                    'sales_order_item|qty_ordered__sum',
                    'sales_order_item|tax_amount__sum',
                    'sales_order_item|discount_amount__sum',
                    'sales_order_item|amount_refunded__sum',
                    'sales_order_item|gross_margin__avg',
                    'sales_order_item|row_total__sum',
                ];

                //                $applicableColumns = $this->columnService->getApplicableColumns($baseDimension);
                for ($it = 0; $it < 10; $it++) {
                    foreach ($baseDimensions as $baseDimension) {

                        $columns = [
                            $baseDimension,
                        ];

                        for ($i = 0; $i < 3; $i++) {
                            $columns[] = $baseColumns[rand(0, count($baseColumns) - 1)];//$applicableColumns[rand(0, count($applicableColumns) - 1)];
                        }

                        $ts = microtime(true);

                        $request = $this->requestBuilder->create()
                            ->setTable('catalog_product_entity');

                        $request->setDimensions([
                            $baseDimension,
                        ]);

                        $request->setColumns($columns);

                        try {

                            $response = $request->process();

                            $time = microtime(true) - $ts;
                            $this->renderResponse($output, $response, $time);

                            $output->writeln($response->getRequest()->getQuery());
                        } catch (\Exception $e) {
                            print_r($request);
                            print_R($e);
                        }
                    }
                }
                */
        //
        //        die();
        //
        //        //        $request->addSortOrder('sales_order|created_at__year', 'asc');
        //        //        $request->addSortOrder('sales_order|status', 'asc');
        //        //        $request->addSortOrder('sales_order|customer_group_id', 'asc');
        //
        //        $baseDimension = 'catalog_product_entity|sku';
        //
        //        foreach ($this->columnService->getApplicableDimensions($baseDimension) as $applicableDimension) {
        //            $ts = microtime(true);
        //
        //            $request = $this->requestBuilder->create()
        //                ->setTable('catalog_product_entity');
        //
        //            $request->setDimensions([
        //                $baseDimension,
        //                $applicableDimension,
        //            ]);
        //
        //            $request
        //                ->addColumn($baseDimension)
        //                ->addColumn($applicableDimension)
        //                ->addColumn('sales_order_item|qty_ordered__sum');
        //
        //            try {
        //
        //                $response = $request->process();
        //
        //                $time = microtime(true) - $ts;
        //                $this->renderResponse($output, $response, $time);
        //
        //                $output->writeln($response->getRequest()->getQuery());
        //            } catch (\Exception $e) {
        //                throw new \Exception($request, 0, $e);
        //            }
        //        }
        //
        //        die();
        //
        //
        //        $reports = array_filter(explode(',', $input->getArgument('report')));
        //
        //
        //
        //    private function processRequest(OutputInterface $output, ReportInterface $report, $columns, $dimension)
        //    {
        //        $request = $this->requestBuilder->create()
        //            ->setTable($report->getTable())
        //            ->setDimensions($dimension);
        //
        //        $request->addColumn($dimension);
        //
        //        foreach ($columns as $columnName) {
        //            $request->addColumn($columnName);
        //        }
        //
        //        try {
        //            $ts       = microtime(true);
        //            $response = $request->process();
        //            $time     = microtime(true) - $ts;
        //
        //            $this->renderResponse($output, $response, $time);
        //        } catch (\Exception $e) {
        //            print_r($request);
        //            print_r($e);
        //        }
    }

    /**
     * @param OutputInterface $output
     * @param ResponseInterface $response
     * @param mixed $time
     */
    private function renderResponse(
        OutputInterface $output,
        ResponseInterface $response,
        $time
    ) {
        $output->writeln("Size: <comment>{$response->getSize()}</comment>");
        $output->writeln("Time: <comment>{$time}</comment>");

        if (count($response->getColumns()) <= 12) {
            $limit   = 2;
            $headers = [];
            foreach ($response->getColumns() as $column) {
                $headers[] = $column->getName();
            }

            $table = new Table($output);
            $table->setHeaders($headers);
            foreach ($response->getItems() as $item) {
                $table->addRow($item->getFormattedData());
                foreach ($item->getItems() as $i) {
                    $table->addRow($i->getFormattedData());
                }
                if ($limit-- <= 0) {
                    break;
                }
            }

            $table->addRow($response->getTotals()->getFormattedData());
            $table->render();
        } else {
            $limit = 5;
            $table = new Table($output);

            $rows = [];
            $idx  = 0;

            foreach ($response->getTotals()->getFormattedData() as $value) {
                $column       = $response->getColumns()[$idx];
                $rows[$idx][] = $column->getLabel();
                $idx++;
            }

            foreach ($response->getItems() as $item) {
                $idx = 0;
                foreach ($item->getFormattedData() as $value) {
                    $rows[$idx][] = substr($value, 0, 10);
                    $idx++;
                }
                if ($limit-- <= 0) {
                    break;
                }
            }

            $idx = 0;
            foreach ($response->getTotals()->getFormattedData() as $value) {
                $rows[$idx][] = $value;
                $idx++;
            }
            $table->addRows($rows);
            $table->render();
            $output->writeln(str_repeat('-', 10));
        }

        $this->validate($response, $output);
    }

    /**
     * @param ResponseInterface $response
     * @param OutputInterface $output
     */
    private function validate(
        ResponseInterface $response,
        OutputInterface $output
    ) {
        foreach ($response->getTotals()->getFormattedData() as $column => $value) {
            $key = $column;

            if (strpos($value, '%') !== false) {
                continue;
            }

            if ($value === null) {
                continue;
            }

            if (strpos($column, '__sum') === false
                && strpos($column, '__cnt') === false
                && strpos($column, '__avg') === false) {
                continue;
            }

            if (!isset($this->totals[$key])) {
                $this->totals[$key] = $value;
                $output->writeln("Set totals $key: <info>$value</info>");
            } else {
                if ($this->totals[$key] != $value) {
                    $output->writeln("<error>Wrong totals $key.</error> Expected: {$this->totals[$key]}, Actual: $value");
                } else {
                    $output->writeln("Match totals $key: <info>$value</info>");
                }
            }
        }
    }
}
