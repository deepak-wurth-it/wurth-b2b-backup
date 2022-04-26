<?php
   
namespace Pim\Category\Model;


class PimCategoryImages extends \Magento\Framework\Model\AbstractModel{

   
   
  /**
    * CMS page cache tag.
    */
   const CACHE_TAG = 'pim_category_images';

   /**
    * @var string
    */
   protected $_cacheTag = 'pim_category_images';

   /**
    * Prefix of model events names.
    *
    * @var string
    */
   protected $_eventPrefix = 'pim_category_images';

   /**
    * Initialize resource model.
    */
   protected function _construct()
   {
       $this->_init(\Pim\Category\Model\ResourceModel\PimCategoryImages::class);
   }

  
}
