<?php

namespace Amasty\Promo\Plugin\Checkout\Block\Cart\Item;

use Amasty\Promo\Model\Prefix;
use Magento\Checkout\Block\Cart\Item\Renderer;

class RendererPlugin
{
    /**
     * @var Prefix
     */
    private $prefix;

    public function __construct(Prefix $prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @param Renderer $subject
     * @param string $result
     *
     * @return string
     */
    public function afterGetProductName(Renderer $subject, $result)
    {
        $item = $subject->getItem();

        if ($item->hasQuoteItem()) {
            $item = $item->getQuoteItem();
        }

        if ($this->prefix->isNeedPrefix($item)) {
            $this->prefix->addPrefixToName($item);
        }

        return $item->getName();
    }
}
