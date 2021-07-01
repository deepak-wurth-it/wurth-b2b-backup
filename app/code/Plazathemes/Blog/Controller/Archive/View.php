<?php
/**
 * Copyright © 2015 PlazaThemes.com. All rights reserved.

 * @author PlazaThemes Team <contact@plazathemes.com>
 */
namespace Plazathemes\Blog\Controller\Archive;

/**
 * Blog archive view
 */
class View extends \Magento\Framework\App\Action\Action
{
    /**
     * View blog archive action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $date = $this->getRequest()->getParam('date');

        $date = explode('-', $date);
        $date[2] = '01';
        $time = strtotime(implode('-', $date));

        if (!$time || count($date) != 3) {
            $this->_forward('index', 'noroute', 'cms');
            return;
        }

        $registry = $this->_objectManager->get('\Magento\Framework\Registry');
        $registry->register('current_blog_archive_year', (int)$date[0]);
        $registry->register('current_blog_archive_month', (int)$date[1]);


        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
