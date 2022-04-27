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
                array('pdf' => $this->getTable('pdfs')),
                'main_table.PdfId = pdf.Id',
               [
                    'name' => 'pdf.Name',
				    'pdf_path' => 'pdf.Path'
                ]

            )
			->where("main_table.ChannelId  = 2")
			->where("main_table.UpdateRequired  = 1")
			->where("main_table.Active  = 1")
			->where("main_table.IsMainPdf  = 1")
			->where("pdf.Active  = 1")
			->where("pdf.UpdateRequired  = 1")
			->order('main_table.Id ASC')
			->distinct(true);

                    //echo  $this->getSelect();exit;
        return $this;
    }
}
