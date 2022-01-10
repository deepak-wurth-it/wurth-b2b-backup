<?php

namespace Wcb\Blog\Controller\Adminhtml\Items;

class Index extends \Wcb\Blog\Controller\Adminhtml\Items
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
        $resultPage->setActiveMenu('Wcb_Blog::test');
        $resultPage->getConfig()->getTitle()->prepend(__('Blog Items'));
        $resultPage->addBreadcrumb(__('Blog'), __('Blog'));
        $resultPage->addBreadcrumb(__('Items'), __('Items'));
        return $resultPage;
    }
}