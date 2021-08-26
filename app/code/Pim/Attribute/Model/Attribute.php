<?php
   
namespace Pim\Attribute\Model;

class Attribute extends \Magento\Framework\Model\AbstractModel{

   
   
   /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'pim_attribute_records';

    /**
     * @var string
     */
    protected $_cacheTag = 'pim_attribute_records';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'pim_attribute_records';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Pim\Attribute\Model\ResourceModel\Attribute::class);
    }

   
}

