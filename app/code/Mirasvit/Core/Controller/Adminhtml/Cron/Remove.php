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



namespace Mirasvit\Core\Controller\Adminhtml\Cron;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory as ScheduleCollectionFactory;

class Remove extends Action
{
    private $scheduleCollectionFactory;

    public function __construct(Context $context, ScheduleCollectionFactory $scheduleCollectionFactory)
    {
        parent::__construct($context);
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $scheduleId = $this->getRequest()->getParam('schedule_id');
        if ($scheduleId) {
            try {
                $collection = $this->scheduleCollectionFactory->create();
                $schedule   = $collection->getItemById($scheduleId);
                if ($schedule) {
                    $schedule->delete();
                    $this->messageManager->addSuccessMessage(__('The record has been removed.'));
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }
}
