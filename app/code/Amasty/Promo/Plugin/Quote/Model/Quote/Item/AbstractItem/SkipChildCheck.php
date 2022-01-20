<?php

declare(strict_types=1);

namespace Amasty\Promo\Plugin\Quote\Model\Quote\Item\AbstractItem;

use Amasty\Promo\Helper\Item;
use Magento\Catalog\Model\Product\Type;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class SkipChildCheck
{
    /**
     * @var Item
     */
    private $promoItemHelper;

    public function __construct(Item $promoItemHelper)
    {
        $this->promoItemHelper = $promoItemHelper;
    }

    /**
     * @param AbstractItem $item
     * @param bool $result
     */
    public function afterIsChildrenCalculated(AbstractItem $item, bool $result): bool
    {
        if ($item->getProductType() == Type::TYPE_BUNDLE && $this->promoItemHelper->isPromoItem($item)) {
            return false;
        }

        return $result;
    }
}
