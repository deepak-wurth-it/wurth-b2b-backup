<?php

declare(strict_types=1);

namespace Amasty\Promo\Plugin\Sales\Model\Order\Creditmemo\Item;

use Amasty\Promo\Model\Order\Item\PromoChecker;
use Magento\Catalog\Model\Product\Type;
use Magento\Sales\Model\Order\Creditmemo\Item as CreditmemoItem;

class ResetBundlePrice
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
     * Reset bundle parent product price to exclude from subtotal
     *
     * @param CreditmemoItem $item
     * @return CreditmemoItem
     */
    public function afterCalcRowTotal(CreditmemoItem $item): CreditmemoItem
    {
        $orderItem = $item->getOrderItem();

        if ($orderItem->getProductType() === Type::TYPE_BUNDLE && $this->promoChecker->isPromoItem($orderItem)) {
            $item
                ->setRowTotal(0)
                ->setBaseRowTotal(0)
                ->setRowTotalInclTax(0)
                ->setBaseRowTotalInclTax(0);
        }

        return $item;
    }
}
