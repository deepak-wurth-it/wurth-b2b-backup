<?php

namespace Wcb\Testimonials\Model\ResourceModel\Testimonials;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'testimonials_id';
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Wcb\Testimonials\Model\Testimonials',
            'Wcb\Testimonials\Model\ResourceModel\Testimonials'
        );
    }
}