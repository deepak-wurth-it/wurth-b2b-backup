<?php

namespace Wcb\Blog\Controller\Adminhtml\Items;

class NewAction extends \Wcb\Blog\Controller\Adminhtml\Items
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
