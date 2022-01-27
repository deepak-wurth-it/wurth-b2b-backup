<?php

namespace Amasty\SalesRuleWizard\Block\Adminhtml\Steps;

class ApplySettings extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    /**
     * {@inheritdoc}
     */
    public function getCaption()
    {
        //Step 3
        return __('Rule Settings');
    }
}
