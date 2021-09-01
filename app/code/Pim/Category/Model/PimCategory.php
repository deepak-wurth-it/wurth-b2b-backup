<?php
   
namespace Pim\Category\Model;


class PimCategory extends \Magento\Framework\Model\AbstractModel{

   
   
  /**
    * CMS page cache tag.
    */
   const CACHE_TAG = 'pim_middleware_category_records';

   /**
    * @var string
    */
   protected $_cacheTag = 'pim_middleware_category_records';

   /**
    * Prefix of model events names.
    *
    * @var string
    */
   protected $_eventPrefix = 'pim_middleware_category_records';

   /**
    * Initialize resource model.
    */
   protected function _construct()
   {
       $this->_init(\Pim\Category\Model\ResourceModel\PimCategory::class);
   }

  
}