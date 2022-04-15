<?php

namespace Wcb\NegotiableQuote\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class RequestQuote extends Template
{
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }
    public function getQuoteUrl()
    {
        return $this->getUrl("negotiable_quote/quote");
    }
}
