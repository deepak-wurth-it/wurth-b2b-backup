<?php
namespace Wcb\Catalogslider\Model\ResourceModel;

class Catalogslider extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('wurth_owlcarouselslider_banners', 'id');
    }
}
?>