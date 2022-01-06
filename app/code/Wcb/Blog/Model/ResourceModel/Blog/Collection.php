<?php

namespace Wcb\Blog\Model\ResourceModel\Blog;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'blog_id';
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Wcb\Blog\Model\Blog',
            'Wcb\Blog\Model\ResourceModel\Blog'
        );
    }
}