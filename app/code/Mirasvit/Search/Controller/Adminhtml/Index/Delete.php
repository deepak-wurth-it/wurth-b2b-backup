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


declare(strict_types=1);

namespace Mirasvit\Search\Controller\Adminhtml\Index;

use Mirasvit\Search\Controller\Adminhtml\AbstractIndex;

class Delete extends AbstractIndex
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $index = $this->initModel();

        $resultRedirect = $this->resultRedirectFactory->create();

        if ($index->getId()) {
            try {
                $this->indexRepository->delete($index);

                $this->messageManager->addSuccessMessage((string)__('The search index has been deleted.'));

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', ['id' => $index->getId()]);
            }
        } else {
            $this->messageManager->addErrorMessage((string)__('This search index no longer exists.'));

            return $resultRedirect->setPath('*/*/');
        }
    }
}
