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



namespace Mirasvit\Core\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Cron\Model\Schedule;
use Mirasvit\Core\Api\Service\CronServiceInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory as ScheduleCollectionFactory;

class Cron extends Template
{
    protected $_template = 'Mirasvit_Core::backend/cron.phtml';

    private   $cronService;

    private   $datetime;

    private   $scheduleCollectionFactory;

    public function __construct(
        ScheduleCollectionFactory $scheduleCollectionFactory,
        CronServiceInterface $cronService,
        DateTime $dateTime,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->cronService               = $cronService;
        $this->datetime                  = $dateTime;
    }

    public function isCronRunning()
    {
        return $this->cronService->isCronRunning();
    }

    public function getGmtDateTime()
    {
        return $this->datetime->gmtDate();
    }

    /**
     * @return Schedule|null
     */
    public function getLastExecutedJob()
    {
        $collection = $this->scheduleCollectionFactory->create();
        $collection
            ->addFieldToFilter('executed_at', ['notnull' => true])
            ->setOrder('executed_at', 'desc')
            ->setPageSize(1);

        return $collection->getFirstItem()->getId() ? $collection->getFirstItem() : null;
    }
}
