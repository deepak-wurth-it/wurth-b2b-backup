<?php
declare(strict_types=1);

namespace Amasty\Promo\Model\Order\Item;

use Amasty\Promo\Model\Rule;
use Magento\Framework\DataObject;
use Magento\Sales\Model\Order\Item;

/**
 * Class PromoChecker contains some logic of Amasty/Promo/Helper/Item but has some unique moments.
 * It's necessary only for order item
 */
class PromoChecker
{
    /**
     * @param Item $item
     * @return bool
     */
    public function isPromoItem(Item $item): bool
    {
        if (!$item->hasData(Rule::OPTION_ID)) {
            $item->setData(Rule::OPTION_ID, $this->getRuleIdFromBuyRequest($item->getBuyRequest()));
        }

        return !!$item->getData(Rule::OPTION_ID);
    }

    /**
     * @param array|DataObject $buyRequest
     * @return int|null
     */
    private function getRuleIdFromBuyRequest($buyRequest): ?int
    {
        return isset($buyRequest['options'][Rule::OPTION_ID])
            ? (int) $buyRequest['options'][Rule::OPTION_ID]
            : null;
    }
}
