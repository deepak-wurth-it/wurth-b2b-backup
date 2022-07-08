<?php
namespace Wcb\BarcodeApi\Model\ResourceModel;
class Barcodes extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('barcodes', 'entity_id');
    }
}