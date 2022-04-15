<?php
   
namespace Pim\Product\Model;

class ProductImages extends \Magento\Framework\Model\AbstractModel{

   
   
   /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'pim_product_images';

    /**
     * @var string
     */
    protected $_cacheTag = 'pim_product_images';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'pim_product_images';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Pim\Product\Model\ResourceModel\ProductImages::class);
    }

   
}

