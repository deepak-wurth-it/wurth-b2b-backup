<?php

namespace Wcb\Blog\Model;

use Magento\Framework\Model\AbstractModel;

class Blog extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Wcb\Blog\Model\ResourceModel\Blog');
    }
}