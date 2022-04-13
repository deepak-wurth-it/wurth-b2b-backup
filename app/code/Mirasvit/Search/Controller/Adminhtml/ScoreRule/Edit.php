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


namespace Mirasvit\Search\Controller\Adminhtml\ScoreRule;

use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Search\Api\Data\ScoreRuleInterface;
use Mirasvit\Search\Controller\Adminhtml\AbstractScoreRule;

class Edit extends AbstractScoreRule
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $model = $this->initModel();
        $id = $this->getRequest()->getParam(ScoreRuleInterface::ID);

        if (!$model->getId() && $id) {
            $this->messageManager->addErrorMessage((string)__('This rule no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('*/*/');
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->initPage($resultPage)
            ->getConfig()->getTitle()->prepend(
                $model->getId() ? (string)__('Rule "%1"', $model->getTitle()) : (string)__('New Rule')
            );

        return $resultPage;
    }
}
