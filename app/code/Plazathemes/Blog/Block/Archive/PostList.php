<?php
/**
 * Copyright © 2015 PlazaThemes.com. All rights reserved.

 * @author PlazaThemes Team <contact@plazathemes.com>
 */

namespace Plazathemes\Blog\Block\Archive;

use Magento\Store\Model\ScopeInterface;

/**
 * Blog archive posts list
 */
class PostList extends \Plazathemes\Blog\Block\Post\PostList
{
    /**
     * Prepare posts collection
     * @return \Plazathemes\Blog\Model\ResourceModel\Post\Collection
     */
	protected function _preparePostCollection()
    {
        parent::_preparePostCollection();
        $this->_postCollection->getSelect()
            ->where('MONTH(publish_time) = ?', $this->getMonth())
            ->where('YEAR(publish_time) = ?', $this->getYear());
    }

    /**
     * Get archive month
     * @return string
     */
    public function getMonth()
    {
        return (int)$this->_coreRegistry->registry('current_blog_archive_month');
    }

    /**
     * Get archive year
     * @return string
     */
    public function getYear()
    {
        return (int)$this->_coreRegistry->registry('current_blog_archive_year');
    }

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $title = $this->_getTitle();
        $this->_addBreadcrumbs($title);
        $this->pageConfig->getTitle()->set($title);

        return parent::_prepareLayout();
    }

    /**
     * Prepare breadcrumbs
     *
     * @param  string $title
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function _addBreadcrumbs($title)
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
            $breadcrumbsBlock->addCrumb('blog_search', ['label' => $title, 'title' => $title]);
        }
    }

    /**
     * Retrieve title
     * @return string
     */
    protected function _getTitle()
    {
        $time = strtotime($this->getYear().'-'.$this->getMonth().'-01');
        return sprintf(
            __('Monthly Archives: %s %s'),
            __(date('F', $time)), date('Y', $time)
        );
    }

}
