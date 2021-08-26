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

use Mirasvit\Brand\Controller\Adminhtml\Brand;

class Delete extends Brand
{
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');

        try {
            $model = $this->brandPageRepository->get($id);
            $this->brandPageRepository->delete($model);

            $this->messageManager->addSuccessMessage((string)__('Brand page was successfully deleted'));
            $this->_redirect('*/*/');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect('*/*/edit', ['id' => $id]);
        }

        $this->_redirect('*/*/');
    }
}
