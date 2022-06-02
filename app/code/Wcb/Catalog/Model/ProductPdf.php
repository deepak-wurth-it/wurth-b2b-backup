<?php

namespace Wcb\Catalog\Model;

class ProductPdf extends \Magento\Framework\Model\AbstractModel{



   /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'wcb_product_pdf';

    /**
     * @var string
     */
    protected $_cacheTag = 'wcb_product_pdf';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wcb_product_pdf';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Wcb\Catalog\Model\ResourceModel\ProductPdf::class);
    }


}

