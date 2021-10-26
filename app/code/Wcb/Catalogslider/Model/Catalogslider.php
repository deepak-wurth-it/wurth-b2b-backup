<?php
namespace Wcb\Catalogslider\Model;

class Catalogslider extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Wcb\Catalogslider\Model\ResourceModel\Catalogslider');
    }
}
?>