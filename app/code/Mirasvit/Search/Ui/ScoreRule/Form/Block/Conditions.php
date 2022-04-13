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



namespace Mirasvit\Search\Ui\ScoreRule\Form\Block;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset as FieldsetRenderer;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Mirasvit\Search\Api\Data\ScoreRuleInterface;
use Mirasvit\Search\Model\ScoreRule\Rule;

class Conditions extends Generic implements TabInterface
{
    private $fieldsetRenderer;

    private $formFactory;

    private $conditionsRenderer;

    private $registry;

    private $formName;

    public function __construct(
        ConditionsRenderer $conditionsRenderer,
        FieldsetRenderer $fieldsetRenderer,
        Context $context,
        Registry $registry,
        FormFactory $formFactory
    ) {
        $this->setNameInLayout('conditions');
        $this->conditionsRenderer = $conditionsRenderer;
        $this->fieldsetRenderer   = $fieldsetRenderer;
        $this->formFactory        = $formFactory;
        $this->registry           = $registry;
        parent::__construct($context, $registry, $formFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle(): string
    {
        return $this->getTabLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden(): bool
    {
        return false;
    }

    protected function _prepareForm(): Generic
    {
        $this->formName = Rule::FORM_NAME;

        /** @var ScoreRuleInterface $scoreRule */
        $scoreRule = $this->registry->registry(ScoreRuleInterface::class);
        $rule      = $scoreRule->getRule();
        $conditionsFieldSetId = 'rule_conditions_fieldset';

        $form = $this->formFactory->create();

        $form->setHtmlIdPrefix('rule_');

        $renderer = $this->fieldsetRenderer
            ->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setData('new_child_url', $this->getUrl('search/scoreRule/newConditionHtml/form/'. $conditionsFieldSetId, [
                'form'      => $conditionsFieldSetId,
                'form_name' => $this->formName,
                'field_set_id' => $conditionsFieldSetId
            ]));

        $fieldset = $form->addFieldset(
            'conditions_fieldset',
            [
                'legend' => __('Apply the rule only for the following products: '),
                'class'  => 'fieldset',
            ]
        )->setRenderer($renderer);

        $rule->getConditions()->setFormName($this->formName);

        $conditionsField = $fieldset->addField('conditions', 'text', [
            'name'           => 'conditions',
            'required'       => true,
            'data-form-part' => $this->formName,
        ]);

        $conditionsField->setRule($rule)
            ->setRenderer($this->conditionsRenderer)
            ->setFormName($this->formName);

        $this->setConditionFormName($rule->getConditions(), $this->formName, $conditionsFieldSetId);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    private function setConditionFormName(\Magento\Rule\Model\Condition\AbstractCondition $conditions, string $formName, string $jsFormName): void
    {
        $conditions->setFormName($formName);
        $conditions->setJsFormObject($jsFormName);
        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName, $jsFormName);
            }
        }
    }

}
