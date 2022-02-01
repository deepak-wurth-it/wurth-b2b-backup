<?php
namespace Wcb\QuantityImport\Model;
use Magento\Framework\Model\AbstractModel;
class QuantityImport extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Wcb\QuantityImport\Model\ResourceModel\QuantityImport');
    }
}