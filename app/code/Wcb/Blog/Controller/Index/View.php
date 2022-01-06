<?php

namespace Wcb\Blog\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NotFoundException;
use Wcb\Blog\Block\BlogView;

class View extends \Magento\Framework\App\Action\Action
{
	protected $_blogview;

	public function __construct(
        Context $context,
        BlogView $blogview
    ) {
        $this->_blogview = $blogview;
        parent::__construct($context);
    }

	public function execute()
    {
    	if(!$this->_blogview->getSingleData()){
    		throw new NotFoundException(__('Parameter is incorrect.'));
    	}
    	
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
