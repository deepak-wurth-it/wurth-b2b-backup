<?php

namespace Wcb\Testimonials\Model;

use Magento\Framework\Model\AbstractModel;

class Testimonials extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Wcb\Testimonials\Model\ResourceModel\Testimonials');
    }
}