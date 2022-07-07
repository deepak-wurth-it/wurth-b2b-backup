<?php

namespace WurthNav\RequisitionQuote\Model;

use Magento\Framework\Model\AbstractModel;

class Quotes extends AbstractModel
{

    /**
     * Shop Contacts cache tag.
     */
    const CACHE_TAG = 'wurthnav_quotes';

    /**
     * @var string
     */
    protected $_cacheTag = 'wurthnav_quotes';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wurthnav_quotes';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Quotes::class);
    }
}
