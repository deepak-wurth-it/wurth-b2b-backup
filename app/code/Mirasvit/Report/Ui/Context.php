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

use Magento\Backend\Model\Session;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Mirasvit\Report\Api\Data\ReportInterface;
use Mirasvit\Report\Api\Repository\ReportRepositoryInterface;
use Mirasvit\Report\Api\Service\ColumnManagerInterface;
use Mirasvit\Report\Model\Config;

class Context
{
    /**
     * @var ColumnManagerInterface
     */
    private $columnManager;
    /**
     * @var ContextInterface
     */
    private $context;
    /**
     * @var Session
     */
    private $session;
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var ReportRepositoryInterface
     */
    private $reportRepository;
    /**
     * @var Config
     */
    private $config;

    /**
     * Context constructor.
     * @param ColumnManagerInterface $columnManager
     * @param Config $config
     * @param ReportRepositoryInterface $reportRepository
     * @param Registry $registry
     * @param Session $session
     * @param ContextInterface $context
     */
    public function __construct(
        ColumnManagerInterface $columnManager,
        Config $config,
        ReportRepositoryInterface $reportRepository,
        Registry $registry,
        Session $session,
        ContextInterface $context
    ) {
        $this->config           = $config;
        $this->reportRepository = $reportRepository;
        $this->registry         = $registry;
        $this->session          = $session;
        $this->context          = $context;
        $this->columnManager    = $columnManager;
    }

    /**
     * @return ReportInterface
     */
    public function getReport()
    {
        $report = $this->registry->registry('current_report');
        if (!$report) {
            $report = $this->reportRepository->get(
                $this->context->getRequestParam('report', 'order_overview')
            );
            $this->registry->register('current_report', $report);
        }

        //        $report->setUiContext($this);
        return $report;
    }

    /**
     * @return string
     */
    public function getActiveDimension()
    {
        if ($dimension = $this->context->getRequestParam('dimension')) {
            return $dimension;
        }

        return $this->getReport()->getPrimaryDimensions();
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Getting data according to the key
     * @param string     $key
     * @param mixed|null $defaultValue
     * @return mixed
     */
    public function getRequestParam($key, $defaultValue = null)
    {
        return $this->context->getRequestParam($key, $defaultValue);
    }

    /**
     * @return ColumnManagerInterface
     */
    public function getColumnManager()
    {
        return $this->columnManager;
    }
}
