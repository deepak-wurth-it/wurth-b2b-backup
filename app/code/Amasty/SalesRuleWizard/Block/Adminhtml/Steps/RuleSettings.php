<?php

namespace Amasty\SalesRuleWizard\Block\Adminhtml\Steps;

class RuleSettings extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    /**
     * {@inheritdoc}
     */
    public function getCaption()
    {
        // Step 2
        return __('Product Settings');
    }
}
