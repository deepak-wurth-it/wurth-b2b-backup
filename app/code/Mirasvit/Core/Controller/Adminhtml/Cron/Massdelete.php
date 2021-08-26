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
use Magento\Ui\Component\MassAction\Filter;
use \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory as ScheduleCollectionFactory;

class Massdelete extends Action
{
    private $scheduleCollectionFactory;

    private $filter;

    public function __construct(
        Context $context,
        ScheduleCollectionFactory $scheduleCollectionFactory,
        Filter $filter
    ) {
        parent::__construct($context);
        $this->filter                    = $filter;
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
    }

    public function execute()
    {
        try {
            $collection     = $this->filter->getCollection($this->scheduleCollectionFactory->create());
            $collectionSize = $collection->getSize();
            foreach ($collection as $schedule) {
                $schedule->delete();
            }
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $this->_redirect('*/*/index');
    }
}
