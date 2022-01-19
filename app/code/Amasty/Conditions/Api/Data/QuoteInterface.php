<?php

namespace Amasty\Conditions\Api\Data;

interface QuoteInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const ITEM_ID = 'id';

    const QUOTE_ID = 'quote_id';

    const PAYMENT_CODE = 'payment_code';

    /**#@-*/

    /**
     * Get quote id
     *
     * @return int
     */
    public function getQuoteId();

    /**
     * Set quote id
     *
     * @param $quoteId
     *
     * @return $this
     */
    public function setQuoteId($quoteId);

    /**
     * Get payment code
     *
     * @return string
     */
    public function getPaymentCode();

    /**
     * Set payment code
     *
     * @param $paymentCode
     *
     * @return $this
     */
    public function setPaymentCode($paymentCode);
}
