<?php
   
namespace WurthNav\Sales\Model;

class OrdersItems extends \Magento\Framework\Model\AbstractModel{

   
   
   /**
     * Shop Contacts cache tag.
     */
    const CACHE_TAG = 'wurthnav_sales_orders_items';

    /**
     * @var string
     */
    protected $_cacheTag = 'wurthnav_sales_orders_items';

    
    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wurthnav_sales_orders_items';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\WurthNav\Sales\Model\ResourceModel\OrdersItems::class);
    }

   
}

