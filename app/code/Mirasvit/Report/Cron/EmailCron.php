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



namespace Mirasvit\Report\Cron;

use Magento\Cron\Model\ScheduleFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterfaceFactory;
use Mirasvit\Report\Api\Service\EmailServiceInterfaceFactory;
use Mirasvit\Report\Model\ResourceModel\Email\CollectionFactory;

class EmailCron
{
    const JOB_CODE = 'report_email';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ScheduleFactory
     */
    private $scheduleFactory;

    /**
     * @var TimezoneInterfaceFactory
     */
    private $timezoneFactory;

    /**
     * @var EmailServiceInterfaceFactory
     */
    private $emailServiceFactory;

    /**
     * EmailCron constructor.
     * @param CollectionFactory $collectionFactory
     * @param ScheduleFactory $scheduleFactory
     * @param TimezoneInterfaceFactory $timezoneFactory
     * @param EmailServiceInterfaceFactory $emailServiceFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ScheduleFactory $scheduleFactory,
        TimezoneInterfaceFactory $timezoneFactory,
        EmailServiceInterfaceFactory $emailServiceFactory
    ) {
        $this->collectionFactory   = $collectionFactory;
        $this->scheduleFactory     = $scheduleFactory;
        $this->timezoneFactory     = $timezoneFactory;
        $this->emailServiceFactory = $emailServiceFactory;
    }

    /**
     * @param bool $verbose
     *
     * @return void
     */
    public function execute($verbose = true)
    {
        $shedule = $this->scheduleFactory->create();

        /** @var \Mirasvit\Report\Model\Email $email */
        foreach ($this->collectionFactory->create() as $email) {
            $email = $email->load($email->getId());

            if (!$email->getIsActive()
                // do not send the same email twice. It's possible when Magento cron does not run every minute.
                || date('Y-m-d H:i', strtotime($email->getLastSentAt())) === date('Y-m-d H:i', time())
            ) {
                continue;
            }

            $shedule
                ->setCronExpr($email->getSchedule())
                ->setScheduledAt($this->timezoneFactory->create()->date()->getTimestamp());

            if ($shedule->trySchedule()) {
                $this->emailServiceFactory->create()->send($email);

                // update last_sent_at field
                $email->setLastSentAt(date('Y-m-d H:i:s', time()));
                $email->save();
            }
        }
    }
}
