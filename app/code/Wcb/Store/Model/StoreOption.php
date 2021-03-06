<?php

namespace Wcb\Store\Model;

//use Magento\Framework\Data\OptionSourceInterface;
use \Magento\Framework\Option\ArrayInterface;
class StoreOption implements  ArrayInterface
{
    public $_options;
    public function __construct(
        \Wcb\Store\Block\Store $store
    ) {
        $this->store = $store;
    }

    public function toOptionArray(): array
    {
        $result = [];
        $storesArray =  $this->store->getStoresArray();
        if($storesArray){

                $result[] = [
                    'value' => null,
                    'label' => 'Please Select store for pickup'
                ];

                // $result[] = [
                //     'value' => '-1',
                //     'label' => 'No store allotment'
                // ];
                
                foreach ($storesArray as $row) {
                    $result[] = [
                        'value' => $row['entity_id'],
                        'label' =>  $row['name']
                    ];
                }

         }
        return $result;
    }

    
}