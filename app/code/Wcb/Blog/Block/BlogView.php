<?php

namespace Wcb\Blog\Block;

use Magento\Framework\View\Element\Template\Context;
use Wcb\Blog\Model\BlogFactory;
use Magento\Cms\Model\Template\FilterProvider;
/**
 * Blog View block
 */
class BlogView extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Blog
     */
    protected $_blog;
    public function __construct(
        Context $context,
        BlogFactory $blog,
        FilterProvider $filterProvider
    ) {
        $this->_blog = $blog;
        $this->_filterProvider = $filterProvider;
        parent::__construct($context);
    }

    public function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Wcb Blog Module View Page'));
        
        return parent::_prepareLayout();
    }

    public function getSingleData()
    {
        $id = $this->getRequest()->getParam('id');
        $blog = $this->_blog->create();
        $singleData = $blog->load($id);
        if($singleData->getBlogId() || $singleData['blog_id'] && $singleData->getStatus() == 1){
            return $singleData;
        }else{
            return false;
        }
    }
}