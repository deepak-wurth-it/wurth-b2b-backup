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

use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Controller\Adminhtml\AbstractIndex;

class Save extends AbstractIndex
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        $resultRedirect = $this->resultRedirectFactory->create();

        if ($this->getRequest()->getParams()) {
            $index = $this->initModel();

            if (!$index->getId() && $id) {
                $this->messageManager->addErrorMessage((string)__('This search index no longer exists.'));

                return $resultRedirect->setPath('*/*/');
            }

            $attributes = $this->getRequest()->getParam('attributes') ?? [];
            $properties = $this->getRequest()->getParam('properties') ?? [];

            $index
                ->setStatus(IndexInterface::STATUS_INVALID)
                ->setTitle($this->getRequest()->getParam(IndexInterface::TITLE))
                ->setIdentifier($this->getRequest()->getParam(IndexInterface::IDENTIFIER))
                ->setIsActive((bool)$this->getRequest()->getParam(IndexInterface::IS_ACTIVE))
                ->setPosition((int)$this->getRequest()->getParam(IndexInterface::POSITION))
                ->setAttributes($attributes)
                ->setProperties($properties);

            try {
                $this->indexRepository->save($index);

                $this->messageManager->addSuccessMessage((string)__('You have saved the search index.'));

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', [IndexInterface::ID => $index->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', [IndexInterface::ID => $id]);
            }
        } else {
            $resultRedirect->setPath('*/*/');
            $this->messageManager->addErrorMessage('No data to save.');

            return $resultRedirect;
        }
    }
}
