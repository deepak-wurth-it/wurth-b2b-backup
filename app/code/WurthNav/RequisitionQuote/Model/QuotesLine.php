<?php

namespace WurthNav\RequisitionQuote\Model;

use Magento\Framework\Model\AbstractModel;

class QuotesLine extends AbstractModel
{

    /**
     * Shop Contacts cache tag.
     */
    const CACHE_TAG = 'wurthnav_quotes_line';

    /**
     * @var string
     */
    protected $_cacheTag = 'wurthnav_quotes_line';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wurthnav_quotes_line';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\QuotesLine::class);
    }
}
