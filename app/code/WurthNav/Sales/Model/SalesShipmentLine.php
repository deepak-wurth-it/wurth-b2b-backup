<?php
   
namespace WurthNav\Sales\Model;

class SalesShipmentLine extends \Magento\Framework\Model\AbstractModel{

   
   
   /**
     * Shop Contacts cache tag.
     */
    const CACHE_TAG = 'wurthnav_sales_shipment_line';

    /**
     * @var string
     */
    protected $_cacheTag = 'wurthnav_sales_shipment_line';

    
    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wurthnav_sales_shipment_line';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\WurthNav\Sales\Model\ResourceModel\SalesShipmentLine::class);
    }

   
}

