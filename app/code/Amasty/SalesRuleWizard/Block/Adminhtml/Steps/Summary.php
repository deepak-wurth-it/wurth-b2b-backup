<?php

namespace Amasty\SalesRuleWizard\Block\Adminhtml\Steps;

class Summary extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    /**
     * {@inheritdoc}
     */
    public function getCaption()
    {
        return __('Summary');
    }

    /**
     * Get Save url
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save');
    }
}
