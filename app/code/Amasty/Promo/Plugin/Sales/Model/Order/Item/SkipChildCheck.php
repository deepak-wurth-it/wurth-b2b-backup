<?php

declare(strict_types=1);

namespace Amasty\Promo\Plugin\Sales\Model\Order\Item;

use Amasty\Promo\Model\Order\Item\PromoChecker;
use Magento\Catalog\Model\Product\Type;
use Magento\Sales\Model\Order\Item as OrderItem;

class SkipChildCheck
{
    /**
     * @var PromoChecker
     */
    private $promoChecker;

    public function __construct(
        PromoChecker $promoChecker
    ) {
        $this->promoChecker = $promoChecker;
    }

    /**
     * Check order item during invoice creating to right discount calculation
     *
     * @param OrderItem $item
     * @param bool $result
     */
    public function afterIsChildrenCalculated(OrderItem $item, bool $result): bool
    {
        if ($item->getProductType() === Type::TYPE_BUNDLE && $this->promoChecker->isPromoItem($item)) {
            return false;
        }

        return $result;
    }
}
