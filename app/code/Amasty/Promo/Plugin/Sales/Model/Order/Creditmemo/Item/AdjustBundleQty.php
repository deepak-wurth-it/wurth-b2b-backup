<?php

declare(strict_types=1);

namespace Amasty\Promo\Plugin\Sales\Model\Order\Creditmemo\Item;

use Amasty\Promo\Model\Order\Creditmemo\Item\Checker as CreditmemoItemChecker;
use Amasty\Promo\Model\Order\Item\PromoChecker;
use Magento\Catalog\Model\Product\Type;
use Magento\Sales\Model\Order\Creditmemo\Item as CreditmemoItem;

class AdjustBundleQty
{
    /**
     * @var PromoChecker
     */
    private $promoChecker;

    /**
     * @var CreditmemoItemChecker
     */
    private $creditmemoItemChecker;

    public function __construct(
        PromoChecker $promoChecker,
        CreditmemoItemChecker $creditmemoItemChecker
    ) {
        $this->promoChecker = $promoChecker;
        $this->creditmemoItemChecker = $creditmemoItemChecker;
    }

    /**
     * @param CreditmemoItem $item
     * @param float $qty
     * @return array
     */
    public function beforeSetQty(CreditmemoItem $item, float $qty): array
    {
        $orderItem = $item->getOrderItem();

        if ($orderItem->getProductType() === Type::TYPE_BUNDLE
            && $this->promoChecker->isPromoItem($orderItem)
            && $this->creditmemoItemChecker->isParentItemToRefund($item)
        ) {
            $qty = $orderItem->getQtyToRefund();
        }

        return [$qty];
    }
}
