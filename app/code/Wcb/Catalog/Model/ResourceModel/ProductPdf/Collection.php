<?php
namespace Wcb\Catalog\Model\ResourceModel\ProductPdf;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    public function _construct()
    {
        $this->_init(\Wcb\Catalog\Model\ProductPdf::class, \Wcb\Catalog\Model\ResourceModel\ProductPdf::class);
    }



}
