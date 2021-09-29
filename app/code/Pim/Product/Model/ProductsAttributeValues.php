<?php

namespace Pim\Product\Model;

class ProductsAttributeValues extends \Magento\Framework\Model\AbstractModel{



   /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'pim_product_attribute_values_records';

    /**
     * @var string
     */
    protected $_cacheTag = 'pim_product_attribute_values_records';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'pim_product_attribute_values_records';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Pim\Product\Model\ResourceModel\ProductsAttributeValues::class);
    }


}

