<?php
   
namespace Pim\Attribute\Model;

class AttributeValues extends \Magento\Framework\Model\AbstractModel{

   
   
   /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'pim_attributevalues_records';

    /**
     * @var string
     */
    protected $_cacheTag = 'pim_attributevalues_records';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'pim_attributevalues_records';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Pim\Attribute\Model\ResourceModel\AttributeValues::class);
    }

   
}

