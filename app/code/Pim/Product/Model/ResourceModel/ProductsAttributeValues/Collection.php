<?php

namespace Pim\Product\Model\ResourceModel\ProductsAttributeValues;

use \Pim\Product\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{

    protected $_idFieldName = 'Id';

    public function _construct()
    {
        $this->_init(\Pim\Product\Model\ProductsAttributeValues::class, \Pim\Product\Model\ResourceModel\ProductsAttributeValues::class);
        $this->_map['fields']['Id'] = 'main_table.Id';
    }

    // protected function _initSelect()
    // {

    //     // $this->getSelect()
    //     //     ->from(['main_table' => $this->getMainTable()])
    //     //     ->join(
    //     //         array("t1" => "productsattributevalues"),
    //     //         "main_table.Id = t1.ProductId",
    //     //         array("AttributeValueId" => "t1.AttributeValueId")
    //     //     )
    //     //     ->distinct(true)
    //     //     ->where("t1.AttributeValueId is not null")
    //     //     ->order('main_table.Id');
    //      //echo  $this->getSelect()->__toString();
    //      //exit;
    //     return $this;
    // }
}
