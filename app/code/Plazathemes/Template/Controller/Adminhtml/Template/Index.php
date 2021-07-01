<?php
namespace Plazathemes\Template\Controller\Adminhtml\Template;
 
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
 

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
         $this->_forward('edit');
    }
}
