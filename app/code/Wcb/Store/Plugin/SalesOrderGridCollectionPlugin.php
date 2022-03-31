<?php

namespace Wcb\Store\Plugin;

use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as SalesOrderGridCollection;

class SalesOrderGridCollectionPlugin
{
    private $messageManager;
    private $collection;
    protected  $adminSession;

    public function __construct(MessageManager $messageManager,
        SalesOrderGridCollection $collection,
        \Magento\Backend\Model\Auth\Session $adminSession
    ) {

        $this->messageManager = $messageManager;
        $this->collection = $collection;
        $this->adminSession = $adminSession;            
    }

    public function aroundGetReport(
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject,
        \Closure $proceed,
        $requestName
    ) {
        $result = $proceed($requestName);
        if ($requestName == 'sales_order_grid_data_source') {
             //print_r($this->adminSession->getUser()->getAclRole());exit;
             $pickup_store_id =   $this->adminSession->getUser()->getData('pickup_store_id');
          
       
             if(!empty($pickup_store_id) && $result instanceof $this->collection){
                      $this->collection->addFieldToFilter('pickup_store_id', array('in' => array($pickup_store_id)));
              }else{
                    $this->collection->addFieldToFilter('pickup_store_id', ['null' => true]);
              }

             return $this->collection;
        }
        return $result;

    }

    //Backup code,Please do not remove
    // public function afterGetReport($subject, $collection, $requestName)
    // {   
    //     if ($requestName !== 'sales_order_grid_data_source') {
    //         return $collection;
    //     }

    //          $pickup_store_id =   $this->adminSession->getUser()->getData('pickup_store_id');
            
    //          if($pickup_store_id){
                 
    //                 $collection->addFieldToFilter('pickup_store_id', array('in' => array($pickup_store_id)));
    //           }

    //     return $collection;
    // }
}
