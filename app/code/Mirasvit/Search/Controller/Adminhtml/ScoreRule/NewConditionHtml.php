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

use Magento\Rule\Model\Condition\AbstractCondition;
use Mirasvit\Search\Controller\Adminhtml\AbstractScoreRule;
use Mirasvit\Search\Model\ScoreRule\Rule;

class NewConditionHtml extends AbstractScoreRule
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));

        $attribute = false;

        $class = $typeArr[0];

        if (count($typeArr) == 2) {
            $attribute = $typeArr[1];
        }

        $model = $this->_objectManager->get($class);

        $model->setId($id)
            ->setType($class)
            ->setRule($this->_objectManager->create(Rule::class))
            ->setPrefix('conditions')
            ->setFormName($this->getRequest()->getParam('form_name', Rule::FORM_NAME));

        if ($attribute) {
            $model->setAttribute($attribute);
        }

        if ($model instanceof AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
