<?php
   
namespace WurthNav\Sales\Model;

class SalesShipmentLineMiddleware extends \Magento\Framework\Model\AbstractModel{

   
   
   /**
     * Shop Contacts cache tag.
     */
    const CACHE_TAG = 'wurthnav_sales_shipment_line_middleware';

    /**
     * @var string
     */
    protected $_cacheTag = 'wurthnav_sales_shipment_line_middleware';

    
    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wurthnav_sales_shipment_line_middleware';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\WurthNav\Sales\Model\ResourceModel\SalesShipmentLineMiddleware::class);
    }

   
}

