<?php
//app/code/SR/Weblog/Model/Resource/BlogPosts.php
namespace  Plazathemes\Hozmegamenu\Model\ResourceModel;
 
class Hozmegamenu extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('hozmegamenu', 'hozmegamenu_id');
    }
}