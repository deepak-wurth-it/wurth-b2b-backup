<?php

namespace Wcb\Blog\Model\ResourceModel;

class Blog extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('wcb_blog', 'blog_id');   //here "wcb_blog" is table name and "blog_id" is the primary key of custom table
    }
}