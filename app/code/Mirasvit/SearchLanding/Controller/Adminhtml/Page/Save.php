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



namespace Mirasvit\SearchLanding\Controller\Adminhtml\Page;

use Mirasvit\SearchLanding\Api\Data\PageInterface;
use Mirasvit\SearchLanding\Controller\Adminhtml\Page;

class Save extends Page
{
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam(PageInterface::ID);

        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->filter($this->getRequest()->getParams());

        if ($data) {
            $model = $this->initModel();

            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage((string)__('This page no longer exists.'));

                return $resultRedirect->setPath('*/*/');
            }

            $model->setQueryText($data[PageInterface::QUERY_TEXT])
                ->setUrlKey($data[PageInterface::URL_KEY])
                ->setTitle($data[PageInterface::TITLE])
                ->setMetaKeywords($data[PageInterface::META_KEYWORDS])
                ->setMetaDescription($data[PageInterface::META_DESCRIPTION])
                ->setLayoutUpdate($data[PageInterface::LAYOUT_UPDATE])
                ->setStoreIds($data[PageInterface::STORE_IDS])
                ->setIsActive((bool)$data[PageInterface::IS_ACTIVE]);

            try {
                $this->pageRepository->save($model);

                $this->messageManager->addSuccessMessage((string)__('You have saved the page.'));

                if ($this->getRequest()->getParam('back') == 'edit') {
                    return $resultRedirect->setPath('*/*/edit', [PageInterface::ID => $model->getId()]);
                }

                return $this->context->getResultRedirectFactory()->create()->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath(
                    '*/*/edit',
                    [PageInterface::ID => $this->getRequest()->getParam(PageInterface::ID)]
                );
            }
        } else {
            $resultRedirect->setPath('*/*/');
            $this->messageManager->addErrorMessage('No data to save.');

            return $resultRedirect;
        }
    }

    private function filter(array $rawData): array
    {
        return $rawData;
    }
}
