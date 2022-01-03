<?php

namespace Wcb\Testimonials\Model\ResourceModel;

class Testimonials extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('wcb_testimonials', 'testimonials_id');   //here "wcb_testimonials" is table name and "testimonials_id" is the primary key of custom table
    }
}