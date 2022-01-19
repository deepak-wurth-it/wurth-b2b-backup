<?php

namespace Amasty\SalesRuleWizard\Block\Adminhtml;

use Magento\Framework\App\ObjectManager;
use Magento\SalesRule\Model\RuleFactory;

class Actions extends \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Actions implements
    \Magento\Ui\Component\Layout\Tabs\TabInterface
{
    /**
     * @var string
     */
    protected $_nameInLayout = 'actions_apply_to';

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = ObjectManager::getInstance()->get(RuleFactory::class)->create();
        $form = $this->addTabToForm($model);
        $this->setForm($form);

        return \Magento\Backend\Block\Widget\Form\Generic::_prepareForm();
    }

    /**
     * Handles addition of actions tab to supplied form.
     *
     * @param \Magento\SalesRule\Model\Rule $model
     * @param string $fieldsetId
     * @param string $formName
     * @return \Magento\Framework\Data\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addTabToForm($model, $fieldsetId = 'actions_fieldset', $formName = 'amasty_promowizard_rule_settings')
    {
        $actionsFieldSetId = $model->getActionsFieldSetId($formName);

        $newChildUrl = $this->getUrl(
            'amasty_promowizard/wizard/newActionHtml/form/' . $actionsFieldSetId,
            ['form_namespace' => $formName]
        );

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        $renderer = $this->_rendererFieldset->setTemplate(
            'Amasty_SalesRuleWizard::promo/fieldset.phtml'
        )->setNewChildUrl(
            $newChildUrl
        )->setFieldSetId(
            $actionsFieldSetId
        );

        $fieldset = $form->addFieldset(
            $fieldsetId,
            []
        )->setRenderer(
            $renderer
        );

        $fieldset->addField(
            'actions',
            'text',
            [
                'name' => 'apply_to',
                'label' => __('Apply To'),
                'title' => __('Apply To'),
                'required' => true,
                'data-form-part' => $formName
            ]
        )->setRule(
            $model
        )->setRenderer(
            $this->_ruleActions
        );

        $this->_eventManager->dispatch('adminhtml_block_salesrule_actions_prepareform', ['form' => $form]);

        $form->setValues($model->getData());
        $this->setActionFormName($model->getActions(), $formName);

        if ($model->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }

        return $form;
    }

    /**
     * Handles addition of form name to action and its actions.
     *
     * @param \Magento\Rule\Model\Condition\AbstractCondition $actions
     * @param string $formName
     * @return void
     */
    private function setActionFormName(\Magento\Rule\Model\Condition\AbstractCondition $actions, $formName)
    {
        $actions->setFormName($formName);
        if ($actions->getActions() && is_array($actions->getActions())) {
            foreach ($actions->getActions() as $condition) {
                $this->setActionFormName($condition, $formName);
            }
        }
    }
}
