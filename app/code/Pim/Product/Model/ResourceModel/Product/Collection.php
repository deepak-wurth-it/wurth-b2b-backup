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
            ->where("main_table.Active  = 1")
            ->where("main_table.UpdateRequired  = 1")
            ->joinLeft(
                array('pdt' => $this->getTable('productdetails')),
                'main_table.Id = pdt.ProductId',
               [
                    'ShortDescription' => 'pdt.ShortDescription',
                    'LongDescription' => 'pdt.LongDescription',
                    'Usage' => 'pdt.Usage',
                    'Instructions' => 'pdt.Instructions',
                    'SeoPageName' => 'pdt.SeoPageName',
                    'MetaTitle' => 'pdt.MetaTitle',
                    'MetaKeywords' => 'pdt.MetaKeywords',
                    'MetaDescription' => 'pdt.MetaDescription',
                    'AlternativeName' => 'pdt.AlternativeName'
                ]

            )
            ->where("pdt.ChannelId = 2")
            ->where("pdt.Active  = 1")
            ->order('main_table.Id ASC')
            ->distinct(true);

        return $this;
    }
}
