<?php
   
namespace Pim\Product\Model;

class ProductPdf extends \Magento\Framework\Model\AbstractModel{

   
   
   /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'pim_productpdf_records';

    /**
     * @var string
     */
    protected $_cacheTag = 'pim_productpdf_records';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'pim_productpdf_records';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Pim\Product\Model\ResourceModel\ProductPdf::class);
    }

   
   
}

