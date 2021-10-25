<?php

namespace Wcb\Catalogslider\Model\ResourceModel\Catalogslider;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Wcb\Catalogslider\Model\Catalogslider', 'Wcb\Catalogslider\Model\ResourceModel\Catalogslider');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>