<?php

namespace Pim\Product\Model\ResourceModel\ProductPdf;

use \Pim\Product\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{

    protected $_idFieldName = 'Id';

    public function _construct()
    {
        $this->_init(\Pim\Product\Model\ProductPdf::class, \Pim\Product\Model\ResourceModel\ProductPdf::class);
        $this->_map['fields']['Id'] = 'main_table.Id';
    }


    protected function _initSelect()
    {

        $this->getSelect()
            ->from(['main_table' => $this->getMainTable()])
            ->joinLeft(
                array('pdfs' => $this->getTable('pdfs')),
                'main_table.PdfId = pdfs.Id',
               [
                    'pdf_name' => 'pdfs.Name',
				    'pdf_path' => 'pdfs.Path',
				    'pdf_type_id' => 'pdfs.PdfTypeId'
                ]

            )
			->where("main_table.ChannelId  = 2")
			->where("main_table.UpdateRequired  = 1")
			->where("main_table.Active  = 1")
			->where("pdfs.Active  = 1")
			->where("pdfs.UpdateRequired  = 1")
			->order('main_table.Id ASC')
			->distinct(true);

        return $this;
    }
}
