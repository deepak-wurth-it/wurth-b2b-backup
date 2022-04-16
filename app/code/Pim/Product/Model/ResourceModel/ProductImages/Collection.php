<?php

namespace Pim\Product\Model\ResourceModel\ProductImages;

use \Pim\Product\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{

    protected $_idFieldName = 'Id';

    public function _construct()
    {
        $this->_init(\Pim\Product\Model\ProductImages::class, \Pim\Product\Model\ResourceModel\ProductImages::class);
        $this->_map['fields']['Id'] = 'main_table.Id';
    }




    protected function _initSelect()
    {

         $this->getSelect()
             ->from(['main_table' => $this->getMainTable()])
            // ->joinLeft(
            //     ["pic" => $this->getTable("pictures")],
            //     'main_table.PictureId =  pic.Id',
            //     ["main_id" => 'main_table.Id']
            //)
            
             ->joinLeft(
                array('pic' => $this->getTable('pictures')),
                'main_table.PictureId = pic.Id',
                [
                    'Path' => 'pic.Path',
                    'ThumbnailPath' => 'pic.ThumbnailPath'
                    //"main_id" => 'main_table.Id'
                    //'*'
                ]

            )
             ->distinct(true)
             ->where("main_table.ChannelId = 2")
             ->where("main_table.Active  = 1")
             ->where("main_table.UpdateRequired  = 1")
             ->where("pic.UpdateRequired  = 1")
             ->where("pic.Active  = 1")
             ->order('main_table.Id ASC');

        
        return $this;
    }
}
