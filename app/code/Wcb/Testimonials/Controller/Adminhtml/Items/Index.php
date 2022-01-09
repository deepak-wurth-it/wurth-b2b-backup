<?php

namespace Wcb\Testimonials\Controller\Adminhtml\Items;

class Index extends \Wcb\Testimonials\Controller\Adminhtml\Items
{
    /**
     * Items list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Wcb_Testimonials::test');
        $resultPage->getConfig()->getTitle()->prepend(__('Testimonials'));
        $resultPage->addBreadcrumb(__('Testimonials'), __('Testimonials'));
        $resultPage->addBreadcrumb(__('Items'), __('Items'));
        return $resultPage;
    }
}