<?php
namespace Wcb\BarcodeApi\Model\ResourceModel\Barcodes;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Wcb\BarcodeApi\Model\Barcodes',
            'Wcb\BarcodeApi\Model\ResourceModel\Barcodes'
        );
    }
}