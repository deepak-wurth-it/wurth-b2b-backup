<?php

namespace Wcb\Checkout\Plugin;

use Magento\Directory\Model\PriceCurrency as magentoPriceCurrency;

class PriceCurrency
{
    public function aroundRound(magentoPriceCurrency $subject, callable $proceed, $price)
    {
        return round($price, 4);
    }
}
