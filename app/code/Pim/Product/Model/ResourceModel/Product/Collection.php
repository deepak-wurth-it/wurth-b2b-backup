<?php

namespace Pim\Product\Model\ResourceModel\Product;

use \Pim\Product\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{

    protected $_idFieldName = 'Id';

    public function _construct()
    {
        $this->_init(\Pim\Product\Model\Product::class, \Pim\Product\Model\ResourceModel\Product::class);
        $this->_map['fields']['Id'] = 'main_table.Id';
    }

     protected function _initSelect()
     {

          $this->getSelect()
              ->from(['main_table' => $this->getMainTable()])
              ->join(
                  array("pav" => "productsattributevalues"),
                  "main_table.Id = pav.ProductId",
                  array("ProductId" => "pav.ProductId")
              )
              ->distinct(true)
              ->where("pav.AttributeValueId is not null")
              ->order('main_table.Id');
          //echo  $this->getSelect()->__toString();
          //exit;
         return $this;
     }
}
