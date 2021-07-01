<?php
/**
 * Copyright © 2015 PlazaThemes.com. All rights reserved.

 * @author PlazaThemes Team <contact@plazathemes.com>
 */

namespace Plazathemes\Blog\Controller\Category;

/**
 * Blog category view
 */
class View extends \Magento\Framework\App\Action\Action
{
    /**
     * View blog category action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $category = $this->_objectManager->create('Plazathemes\Blog\Model\Category')->load($id);
        if (!$category->getId()) {
            $this->_forward('index', 'noroute', 'cms');
            return;
        }

        $this->_objectManager->get('\Magento\Framework\Registry')->register('current_blog_category', $category);

        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
