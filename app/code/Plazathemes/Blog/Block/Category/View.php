<?php
/**
 * Copyright © 2015 PlazaThemes.com. All rights reserved.

 * @author PlazaThemes Team <contact@plazathemes.com>
 */

namespace Plazathemes\Blog\Block\Category;

use Magento\Store\Model\ScopeInterface;

/**
 * Blog category view
 */
class View extends \Plazathemes\Blog\Block\Post\PostList
{
    /**
     * Prepare posts collection
     *
     * @return void
     */
    protected function _preparePostCollection()
    {
        parent::_preparePostCollection();
        if ($category = $this->getCategory()) {
            $this->_postCollection->addCategoryFilter($category);
        }
    }

    /**
     * Retrieve category instance
     *
     * @return \Plazathemes\Blog\Model\Category
     */
    public function getCategory()
    {
        return $this->_coreRegistry->registry('current_blog_category');
    }

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $category = $this->getCategory();
        $this->_addBreadcrumbs($category);
        $this->pageConfig->addBodyClass('blog-category-' . $category->getIdentifier());
        $this->pageConfig->getTitle()->set($category->getTitle());
        $this->pageConfig->setKeywords($category->getMetaKeywords());
        $this->pageConfig->setDescription($category->getMetaDescription());

        return parent::_prepareLayout();
    }

    /**
     * Prepare breadcrumbs
     *
     * @param \Plazathemes\Blog\Model\Category $category
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function _addBreadcrumbs($category)
    {
        if ($this->_scopeConfig->getValue('web/default/show_cms_breadcrumbs', ScopeInterface::SCOPE_STORE)
            && ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs'))
        ) {
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );
            $breadcrumbsBlock->addCrumb(
                'blog',
                [
                    'label' => __('Blog'),
                    'title' => __('Go to Blog Home Page'),
                    'link' => $this->_storeManager->getStore()->getUrl('blog')
                ]
            );
            $breadcrumbsBlock->addCrumb('blog_category',[
                'label' => $category->getTitle(),
                'title' => $category->getTitle()
            ]);
        }
    }
}
