<?php

namespace Wcb\Testimonials\Controller\Adminhtml\Items;

class NewAction extends \Wcb\Testimonials\Controller\Adminhtml\Items
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
