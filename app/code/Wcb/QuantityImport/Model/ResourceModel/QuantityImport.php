<?php
namespace Wcb\QuantityImport\Model\ResourceModel;
class QuantityImport extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('quantity_import', 'entity_id');
    }
}