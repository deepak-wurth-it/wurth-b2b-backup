<?php
namespace Wcb\BarcodeApi\Model;
use Magento\Framework\Model\AbstractModel;
class Barcodes extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Wcb\BarcodeApi\Model\ResourceModel\Barcodes');
    }
}
