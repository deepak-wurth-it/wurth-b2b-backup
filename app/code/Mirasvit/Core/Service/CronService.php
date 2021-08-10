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
 * @package   mirasvit/module-core
 * @version   1.2.122
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Core\Service;

use Magento\Cron\Model\Config as CronConfig;
use Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory as ScheduleCollectionFactory;
use Magento\Cron\Model\Schedule;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Mirasvit\Core\Api\Service\CronServiceInterface;
use Magento\Framework\UrlInterface;

class CronService implements CronServiceInterface
{
    const LIMIT_HOURS   = 6;
    const LAST_JOBS_QTY = 5;

    private $scheduleCollectionFactory;

    private $dateTime;

    private $messageManager;

    private $cronConfig;

    private $scopeConfig;

    private $timezoneConverter;

    private $schedule;

    private $dateTimeFactory;

    protected $urlBuilder;

    public function __construct(
        ScheduleCollectionFactory $scheduleCollectionFactory,
        DateTime $dateTime,
        MessageManagerInterface $messageManager,
        CronConfig $cronConfig,
        ScopeConfigInterface $scopeConfig,
        TimezoneInterface $timezoneConverter,
        Schedule $schedule,
        UrlInterface $urlBuilder,
        DateTimeFactory $dateTimeFactory
    ) {
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->dateTime                  = $dateTime;
        $this->messageManager            = $messageManager;
        $this->cronConfig                = $cronConfig;
        $this->scopeConfig               = $scopeConfig;
        $this->timezoneConverter         = $timezoneConverter;
        $this->schedule                  = $schedule;
        $this->urlBuilder                = $urlBuilder;
        $this->dateTimeFactory           = $dateTimeFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function isCronRunning(array $jobCodes = [])
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\Filesystem\DirectoryList $directory */
        $directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
        $logPath   = $directory->getPath('log');
        $file      = fopen($logPath . '/last_jobs.log', 'a+');
        // cron is not working at all
        if (!$this->isAnyCronRunning()) {
            return false;
        }

        $jobCodesToCheck = [];
        foreach ($jobCodes as $code) {
            if ($this->canCheck($code)) {
                $jobCodesToCheck[] = $code;
            }
        }

        $jobsToCheck = [];
        // checking only jobs with time interval between running < self::LIMIT_HOURS hours
        foreach ($jobCodesToCheck as $code) {
            if ($this->isLastJobsFailed($code)) {
                return false;
            }

            $job = $this->getSuccessfulJobsCollection()
                ->addFieldToFilter('job_code', $code)
                ->getFirstItem();

            if ($job->getId()) {
                $jobsToCheck[] = $job;
            }
        }

        if (!count($jobsToCheck)) {
            // in case we checking only one job which not running
            foreach ($jobCodesToCheck as $jobCode) {
                if ($this->getUnfinishedJobsByCode($jobCode)->getSize() > 1) {
                    return false;
                }
            }

            // cron is working but our jobs wasn't generated yet (fresh installation)
            if ($this->isAnyCronRunning()) {
                return true;
            }
        }

        /** @var Schedule $job */
        foreach ($jobsToCheck as $job) {
            if (!$this->isScheduleInTimeFrame($job)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $moduleName
     * @param string $prefix
     *
     * @return void
     */
    public function outputCronStatus($moduleName, $prefix = '')
    {
        $jobCodes = $this->retrieveJobNames($moduleName);

        $notRunningJobs = [];
        foreach ($jobCodes as $jobCode) {
            if (!$this->isCronRunning([$jobCode])) {
                $notRunningJobs[] = $jobCode;
            }
        }

        if (count($notRunningJobs)) {
            $message = '';

            if ($prefix) {
                $message .= $prefix . ' ';
            }

            if (count($notRunningJobs) == count($jobCodes)) {
                $message .= __(
                    'Cron for Magento is not running. To setup a cron job, follow the link %1',
                    'http://devdocs.magento.com/guides/v2.0/config-guide/cli/config-cli-subcommands-cron.html'
                );
            } else {
                $connection = $this->scheduleCollectionFactory->create()->getConnection();
                $message    .= __(
                    'Cron jobs [' . implode(', ', $notRunningJobs) . '] are not running. '
                    . 'Please check the table ' . $connection->getTableName('cron_schedule')
                    . ' for frozen jobs and remove them.'
                );
            }

            $this->messageManager->addComplexErrorMessage(
                'mstCronMessage',
                [
                    'message' => $message,
                    'url' => $this->urlBuilder->getUrl('mstcore/cron'),
                ]
            );
        }
    }

    /**
     * @return bool
     */
    private function isAnyCronRunning()
    {
        /** @var \Magento\Cron\Model\Schedule $schedule */
        $schedule = $this->getSuccessfulJobsCollection()->getFirstItem();

        return $schedule->getId() ? $this->isScheduleInTimeFrame($schedule) : false;
    }

    /**
     * @param Schedule $schedule
     *
     * @return bool
     */
    private function isScheduleInTimeFrame(Schedule $schedule)
    {
        $jobTimestamp = strtotime($schedule->getExecutedAt()); //in store timezone
        $timestamp    = strtotime($this->dateTime->gmtDate()); //in store timezone

        if (abs($timestamp - $jobTimestamp) > self::LIMIT_HOURS * 60 * 60) {
            return false;
        }

        return true;
    }

    /**
     * @return \Magento\Cron\Model\ResourceModel\Schedule\Collection
     */
    private function getSuccessfulJobsCollection()
    {
        $collection = $this->scheduleCollectionFactory->create();
        $collection
            ->addFieldToFilter('status', 'success')
            ->setOrder('scheduled_at', 'desc')
            ->setPageSize(1);

        return $collection;
    }

    /**
     * @param string $jobCode
     *
     * @return \Magento\Cron\Model\ResourceModel\Schedule\Collection
     */
    private function getUnfinishedJobsByCode($jobCode)
    {
        $collection = $this->scheduleCollectionFactory->create();
        $collection
            ->addFieldToFilter('status', ['neq' => 'success'])
            ->addFieldToFilter('job_code', $jobCode)
            ->setOrder('scheduled_at', 'desc')
            ->setPageSize(1);

        return $collection;
    }

    /**
     * @param string $jobCode
     *
     * @return bool
     */
    private function isLastJobsFailed($jobCode)
    {
        $collection = $this->scheduleCollectionFactory->create();
        $collection
            ->addFieldToFilter('job_code', $jobCode)
            ->setOrder('executed_at', 'desc')
            ->setPageSize(self::LAST_JOBS_QTY);

        $failedJobsCount = 0;
        /** @var \Magento\Cron\Model\Schedule $job */
        foreach ($collection as $job) {
            if ($job->getStatus() == 'error') {
                $failedJobsCount++;
            }
        }

        return $failedJobsCount == self::LAST_JOBS_QTY;
    }

    /**
     * @param string $moduleName
     *
     * @return array
     */
    private function retrieveJobNames($moduleName)
    {
        $jobs = [];

        $moduleNamespace = str_replace("_", "\\", $moduleName);

        foreach ($this->cronConfig->getJobs() as $group) {
            $filtered = array_filter(
                $group,
                function ($params) use ($moduleNamespace) {
                    return isset($params['instance'])
                        ? strpos($params['instance'], $moduleNamespace) !== false
                        : false;
                }
            );

            if (count($filtered)) {
                $jobs = array_merge($jobs, array_keys($filtered));
            }
        }

        return $jobs;
    }

    /**
     * Checks that job was meant to execute during last self::LIMIT_HOURS hours
     *
     * @param string $jobCode
     *
     * @return bool
     */
    private function canCheck($jobCode)
    {
        if ($expression = $this->getExpression($jobCode)) {
            $expressionArray = explode(' ', $expression);

            if ($expressionArray[2] !== '*' && $expressionArray[3] !== '*' && $expressionArray[4] !== '*') {
                // can't check cron status if day, month or day of week is not set as each
                return false;
            }

            $hours = $expressionArray[1];

            $configTimeZone = $this->timezoneConverter->getConfigTimezone();
            $storeDateTime  = $this->dateTimeFactory->create(null, new \DateTimeZone($configTimeZone));

            //we check last self::LIMIT_HOURS hours
            for ($limit = 0; $limit <= self::LIMIT_HOURS; $limit++) {
                $time  = $storeDateTime->setTimestamp(time() - ($limit * 60 * 60));
                $match = $this->schedule->matchCronExpression($hours, $time->format('H'));

                if ($match) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param string $jobCode
     *
     * @return string|false
     */
    private function getExpression($jobCode)
    {
        foreach ($this->cronConfig->getJobs() as $group) {
            if (array_key_exists($jobCode, $group)) {
                return isset($group[$jobCode]['schedule'])
                    ? $group[$jobCode]['schedule']
                    : $this->scopeConfig->getValue(
                        $group[$jobCode]['config_path'],
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );
            }
        }

        return false;
    }
}
