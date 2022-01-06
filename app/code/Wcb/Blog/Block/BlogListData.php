<?php

namespace Wcb\Blog\Block;

use Magento\Framework\View\Element\Template\Context;
use Wcb\Blog\Model\BlogFactory;
/**
 * Blog List block
 */
class BlogListData extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Blog
     */
    protected $_blog;
    public function __construct(
        Context $context,
        BlogFactory $blog
    ) {
        $this->_blog = $blog;
        parent::__construct($context);
    }

    public function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Wcb Blog Module List Page'));
        
        if ($this->getBlogCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'wcb.blog.pager'
            )->setAvailableLimit(array(5=>5,10=>10,15=>15))->setShowPerPage(true)->setCollection(
                $this->getBlogCollection()
            );
            $this->setChild('pager', $pager);
            $this->getBlogCollection()->load();
        }
        return parent::_prepareLayout();
    }

    public function getBlogCollection()
    {
        $page = ($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit'))? $this->getRequest()->getParam('limit') : 5;

        $blog = $this->_blog->create();
        $collection = $blog->getCollection();
        $collection->addFieldToFilter('status','1');
        //$blog->setOrder('blog_id','ASC');
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);

        return $collection;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    
	/**
	 * @return
	 */
	public function getMediaFolder() {
		$media_folder = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
		return $media_folder;
	}
}