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
 * @package   mirasvit/module-navigation
 * @version   2.0.12
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Brand\Controller\Adminhtml\Brand;

use Mirasvit\Brand\Api\Data\BrandPageInterface;
use Mirasvit\Brand\Controller\Adminhtml\Brand;

class Save extends Brand
{
    /**
     * @return void
     */
    public function execute()
    {
        $id             = $this->getRequest()->getParam(BrandPageInterface::ID);
        $resultRedirect = $this->resultRedirectFactory->create();
        $data           = $this->getRequest()->getPostValue();

        if ($data) {
            $data  = $this->postDataProcessor->preparePostData($data);
            $model = $this->initModel();

            if ($id && !$model) {
                $this->messageManager->addErrorMessage((string)__('This brand page no longer exists.'));

                return $resultRedirect->setPath('*/*/');
            }
            $model->setData($data);

            try {
                $this->brandPageRepository->save($model);

                $this->messageManager->addSuccessMessage((string)__('Brand page was saved.'));

                if ($this->getRequest()->getParam('back') == 'edit') {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
    }
}
