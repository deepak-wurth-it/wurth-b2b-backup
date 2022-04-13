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
 * @package   mirasvit/module-search-ultimate
 * @version   2.0.56
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Controller\Adminhtml\Stopword;

use Mirasvit\Search\Api\Data\StopwordInterface;
use Mirasvit\Search\Controller\Adminhtml\AbstractStopword;

class Save extends AbstractStopword
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam(StopwordInterface::ID);

        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $model = $this->initModel();

            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage((string)__('This stopword no longer exists.'));

                return $resultRedirect->setPath('*/*/');
            }

            $model->setTerm($this->getRequest()->getParam(StopwordInterface::TERM))
                ->setStoreId($this->getRequest()->getParam(StopwordInterface::STORE_ID));

            try {
                $this->stopwordRepository->save($model);

                $this->messageManager->addSuccessMessage((string)__('You have saved the stopword.'));

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', [StopwordInterface::ID => $id]);
            }
        } else {
            $resultRedirect->setPath('*/*/');
            $this->messageManager->addErrorMessage('No data to save.');

            return $resultRedirect;
        }
    }
}
