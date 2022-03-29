<?php

namespace Wcb\Store\Model;

use Magento\Framework\Model\AbstractModel;

class StoreOption extends AbstractModel
{
    public $_options;
    public function __construct(
        \Wcb\Store\Block\Store $store
    ) {
        $this->store = $store;
    }


    /**
     * @return array
     */
    public function getAllOptions()
    {
        $storesArray =  $this->store->getStoresArray();
        $option[] =  array('label'=>"Please Select store for pickup",'value'=>'');
            if($storesArray){
                foreach($storesArray as $row){
                    $label = $row['name'];
                    $value = $row['entity_id'];
                    $option[] = array('label'=>$label,'value'=>$value);
                }
                $this->_options = $option;
            }
        return $this->_options;
    }


    
}