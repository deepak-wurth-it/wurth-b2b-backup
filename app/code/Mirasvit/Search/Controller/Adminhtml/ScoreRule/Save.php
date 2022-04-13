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

use Mirasvit\Search\Api\Data\ScoreRuleInterface;
use Mirasvit\Search\Controller\Adminhtml\AbstractScoreRule;

class Save extends AbstractScoreRule
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam(ScoreRuleInterface::ID);

        $model = $this->initModel();

        $data = $this->getRequest()->getPostValue();

        $data = $this->filter($data, $model);

        if ($data) {
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage((string)__('This rule no longer exists.'));

                return $resultRedirect->setPath('*/*/');
            }

            $model->setTitle($data[ScoreRuleInterface::TITLE])
                ->setIsActive($data[ScoreRuleInterface::IS_ACTIVE])
                ->setActiveFrom($data[ScoreRuleInterface::ACTIVE_FROM])
                ->setActiveTo($data[ScoreRuleInterface::ACTIVE_TO])
                ->setStoreIds($data[ScoreRuleInterface::STORE_IDS])
                ->setScoreFactor($data[ScoreRuleInterface::SCORE_FACTOR])
                ->setConditionsSerialized($data[ScoreRuleInterface::CONDITIONS_SERIALIZED])
                ->setPostConditionsSerialized($data[ScoreRuleInterface::POST_CONDITIONS_SERIALIZED]);

            try {
                $this->scoreRuleRepository->save($model);

                $this->messageManager->addSuccessMessage((string)__('You have saved the rule.'));

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', [ScoreRuleInterface::ID => $model->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', [ScoreRuleInterface::ID => $model->getId()]);
            }
        } else {
            $resultRedirect->setPath('*/*/');
            $this->messageManager->addErrorMessage('No data to save.');

            return $resultRedirect;
        }
    }

    /**
     * @param array              $data
     * @param ScoreRuleInterface $scoreRule
     *
     * @return array
     */
    private function filter(array $data, ScoreRuleInterface $scoreRule)
    {
        $scoreFactorType       = $data['score_factor_type'];
        $scoreFactorUnit       = $data['score_factor_unit'];
        $scoreFactorRelatively = $data['score_factor_relatively'];

        if ($scoreFactorType == '+') {
            if ($scoreFactorUnit == '*') {
                $p = '*';
            } else {
                $p = '+';
            }
        } else {
            if ($scoreFactorUnit == '*') {
                $p = '/';
            } else {
                $p = '-';
            }
        }

        $data[ScoreRuleInterface::SCORE_FACTOR] = implode('|', [
            $p, $data['score_factor'], $scoreFactorRelatively]);

        $rule = $scoreRule->getRule();
        if (isset($data['rule']) && isset($data['rule']['conditions'])) {
            $rule->loadPost(['conditions' => $data['rule']['conditions']]);

            $conditions = $rule->getConditions()->asArray();

            $conditions = \Zend_Json::encode($conditions);

            $data[ScoreRuleInterface::CONDITIONS_SERIALIZED] = $conditions;
        }

        if (isset($data['rule']) && isset($data['rule']['post_conditions'])) {
            $rule->loadPost(['actions' => $data['rule']['post_conditions']]);

            $postConditions = $rule->getActions()->asArray();

            $postConditions = \Zend_Json::encode($postConditions);

            $data[ScoreRuleInterface::POST_CONDITIONS_SERIALIZED] = $postConditions;
        }

        return $data;
    }
}
