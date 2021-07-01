<?php
/**
 * Copyright © 2015 PlazaThemes.com. All rights reserved.

 * @author PlazaThemes Team <contact@plazathemes.com>
 */

namespace Plazathemes\Blog\Block\Post\View;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\View\Element\AbstractBlock;

/**
 * Blog post related posts block
 */
class RelatedPosts extends \Plazathemes\Blog\Block\Post\PostList\AbstractList
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->setPageSize(5);
        return parent::_construct();
    }

    /**
     * Prepare posts collection
     *
     * @return void
     */
    protected function _preparePostCollection()
    {
        parent::_preparePostCollection();
        $this->_postCollection
            ->addFieldToFilter('post_id', array('in' => $this->getPost()->getRelatedPostIds() ?: array(0)))
            ->addFieldToFilter('post_id', array('neq' => $this->getPost()->getId()))
            ->setPageSize(
                (int) $this->_scopeConfig->getValue(
                    'mfblog/post_view/related_posts/number_of_posts',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            );
    }

    /**
     * Retrieve true if Display Related Posts enabled
     * @return boolean
     */
    public function displayPosts()
    {
        return (bool) $this->_scopeConfig->getValue(
            'mfblog/post_view/related_posts/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve posts instance
     *
     * @return \Plazathemes\Blog\Model\Category
     */
    public function getPost()
    {
        if (!$this->hasData('post')) {
            $this->setData('post',
                $this->_coreRegistry->registry('current_blog_post')
            );
        }
        return $this->getData('post');
    }

    /**
     * Get Block Identities
     * @return Array
     */
    public function getIdentities()
    {
        return [\Magento\Cms\Model\Page::CACHE_TAG . '_relatedposts_'.$this->getPost()->getId()  ];
    }
}
