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



namespace Mirasvit\Report\Controller\Adminhtml\Email;

use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Report\Controller\Adminhtml\Email;

class Edit extends Email
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $model = $this->initModel();

        if ($this->getRequest()->getParam('id')) {
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This email no longer exists.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
        }

        $this->initPage($resultPage)->getConfig()->getTitle()->prepend(
            $model->getId() ? __('Edit Email "%1"', $model->getTitle()) : __('New Email Report')
        );

        return $resultPage;
    }
}
