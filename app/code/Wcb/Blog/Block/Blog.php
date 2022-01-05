<?php

namespace Wcb\Blog\Block;

/**
 * Blog content block
 */
class Blog extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context
    ) {
        parent::__construct($context);
    }

    public function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Wcb Blog Module'));
        
        return parent::_prepareLayout();
    }
}
