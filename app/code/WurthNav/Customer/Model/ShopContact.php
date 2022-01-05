<?php
   
namespace WurthNav\Customer\Model;

class ShopContact extends \Magento\Framework\Model\AbstractModel{

   
   
   /**
     * Shop Contacts cache tag.
     */
    const CACHE_TAG = 'wurthnav_shop_contact';

    /**
     * @var string
     */
    protected $_cacheTag = 'wurthnav_shop_contact';

    
    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wurthnav_shop_contact';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\WurthNav\Customer\Model\ResourceModel\ShopContact::class);
    }

   
}

