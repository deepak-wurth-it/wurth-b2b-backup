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

use Magento\Framework\App\ObjectManager;
use Mirasvit\Search\Api\Data\ScoreRuleInterface;
use Mirasvit\Search\Controller\Adminhtml\AbstractScoreRule;
use Mirasvit\Search\Model\ScoreRule\Indexer\ScoreRuleIndexer;

class Apply extends AbstractScoreRule
{
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $model = $this->initModel();

        if (!$model->getId()) {
            $this->messageManager->addErrorMessage((string)__('This rule no longer exists.'));

            return $resultRedirect->setPath('*/*/');
        }

        try {
            $objectManager = ObjectManager::getInstance();

            /** @var ScoreRuleIndexer $scoreRuleIndexer */
            $scoreRuleIndexer = $objectManager->create(ScoreRuleIndexer::class);
            $scoreRuleIndexer->execute($model, []);

            $this->messageManager->addSuccessMessage((string)__('You have applied the rule.'));

            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', [ScoreRuleInterface::ID => $model->getId()]);
            }

            return $resultRedirect->setPath('*/*/');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $resultRedirect->setPath('*/*/');
        }
    }
}
