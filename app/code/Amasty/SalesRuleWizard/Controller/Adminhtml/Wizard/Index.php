<?php

namespace Amasty\SalesRuleWizard\Controller\Adminhtml\Wizard;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_SalesRuleWizard::wizard';

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_SalesRuleWizard::wizard');
        $resultPage->getConfig()->getTitle()->prepend(__('Cart Price Rule Wizard'));

        return $resultPage;
    }
}
