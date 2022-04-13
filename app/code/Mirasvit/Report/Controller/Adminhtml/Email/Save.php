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

use Mirasvit\Report\Api\Data\EmailInterface;
use Mirasvit\Report\Controller\Adminhtml\Email;

class Save extends Email
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $id             = $this->getRequest()->getParam(EmailInterface::ID);
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $model = $this->initModel();

            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This email report no longer exists.'));

                return $resultRedirect->setPath('*/*/');
            }

            if (!isset($data['blocks']) || !is_array($data['blocks'])) {
                $data['blocks'] = [];
            }

            $model->addData($data);

            try {
                $this->emailRepository->save($model);

                $this->messageManager->addSuccessMessage(__('You have saved the email report.'));

                if ($this->getRequest()->getParam('back') == 'send') {
                    $this->emailService->send($model);
                }

                return $resultRedirect->setPath('*/*/edit', [EmailInterface::ID => $model->getId()]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', [EmailInterface::ID => $id]);
            }
        } else {
            $resultRedirect->setPath('*/*/');
            $this->messageManager->addErrorMessage('No data to save.');

            return $resultRedirect;
        }
    }
}
