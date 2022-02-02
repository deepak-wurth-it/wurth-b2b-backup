<?php
namespace Wcb\QuantityImport\Model\ResourceModel\QuantityImport;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Wcb\QuantityImport\Model\QuantityImport',
            'Wcb\QuantityImport\Model\ResourceModel\QuantityImport'
        );
    }
}