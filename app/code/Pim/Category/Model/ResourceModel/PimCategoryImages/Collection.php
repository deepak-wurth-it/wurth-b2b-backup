<?php

namespace Pim\Category\Model\ResourceModel\PimCategoryImages;

use \Pim\Category\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection {

    protected $_idFieldName = 'Id';

    public function _construct() {
        $this->_init(\Pim\Category\Model\PimCategoryImages::class, \Pim\Category\Model\ResourceModel\PimCategoryImages::class);
        $this->_map['fields']['Id'] = 'main_table.Id';
    }
    
    
    protected function _initSelect()
    
    {

         $this->getSelect()
             ->from(['main_table' => $this->getMainTable()])
            ->joinLeft(
                array('pic' => $this->getTable('pictures')),
                'main_table.PictureId = pic.Id',
                [
                    'Path' => 'pic.Path',
                    'ThumbnailPath' => 'pic.ThumbnailPath'
                ]

            )
             ->distinct(true)
             ->where("main_table.Active  = 1")
             ->where("main_table.UpdateRequired  = 1")
             ->where("pic.UpdateRequired  = 1")
             ->where("pic.Active  = 1")
             ->order('main_table.Id ASC');

        
        return $this;
    }

}
