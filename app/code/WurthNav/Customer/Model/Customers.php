<?php
   
namespace WurthNav\Customer\Model;

class Customers extends \Magento\Framework\Model\AbstractModel{

   
   
   /**
     * Shop Contacts cache tag.
     */
    const CACHE_TAG = 'wurthnav_customers';

    /**
     * @var string
     */
    protected $_cacheTag = 'wurthnav_customers';

    
    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wurthnav_customers';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\WurthNav\Customer\Model\ResourceModel\Customers::class);
    }

   
}

