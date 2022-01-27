<?php

namespace Amasty\Conditions\Model;

use Amasty\Conditions\Api\Data\QuoteInterface;
use Amasty\Conditions\Model\ResourceModel\Quote as QuoteResourceModel;
use Magento\Framework\Model\AbstractModel;

class Quote extends AbstractModel implements QuoteInterface
{
    public function _construct()
    {
        $this->_init(QuoteResourceModel::class);
    }
    /**
     * @inheritdoc
     */
    public function getQuoteId()
    {
        return $this->_getData(QuoteInterface::QUOTE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setQuoteId($quoteId)
    {
        $this->setData(QuoteInterface::QUOTE_ID, $quoteId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPaymentCode()
    {
        return $this->_getData(QuoteInterface::PAYMENT_CODE);
    }

    /**
     * @inheritdoc
     */
    public function setPaymentCode($paymentCode)
    {
        $this->setData(QuoteInterface::PAYMENT_CODE, $paymentCode);

        return $this;
    }
}
