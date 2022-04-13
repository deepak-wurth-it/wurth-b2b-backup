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



namespace Mirasvit\Report\Repository;

use Magento\Framework\ObjectManagerInterface;
use Mirasvit\Report\Api\Data\ReportInterface;
use Mirasvit\Report\Api\Repository\ReportRepositoryInterface;

class ReportRepository implements ReportRepositoryInterface
{
    /**
     * @var ReportInterface[]
     */
    private $pool = [];

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string[]
     */
    private $reports;

    /**
     * ReportRepository constructor.
     * @param ObjectManagerInterface $objectManager
     * @param array $reports
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $reports = []
    ) {
        $this->objectManager = $objectManager;
        $this->reports       = $reports;
    }

    /**
     * {@inheritdoc}
     */
    public function get($identifier)
    {
        foreach ($this->getList() as $report) {
            if ($report->getIdentifier() == strtolower($identifier)) {
                $report->init();

                return $report;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getList()
    {
        $this->initPool();

        return $this->pool;
    }

    /**
     * @return $this
     */
    private function initPool()
    {
        if (count($this->pool)) {
            return $this;
        }

        foreach ($this->reports as $report) {
            $this->pool[] = $this->objectManager->get($report);
        }

        return $this;
    }
}
