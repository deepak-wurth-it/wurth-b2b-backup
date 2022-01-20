<?php

namespace Amasty\Promo\Plugin\Reorder;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection;

class ReorderItemsCleaner
{

    /**
     * @param Order $subject
     * @param Collection $collection
     *
     * @return Collection
     */
    public function afterGetItemsCollection(Order $subject, Collection $collection)
    {
        if (\Amasty\Promo\Model\Storage::$isReorder) {
            /** @var \Magento\Sales\Model\Order\Item $product */
            foreach ($collection->getItems() as $product) {
                $infoBuyRequest = $product->getProductOptionByCode('info_buyRequest');
                if (isset($infoBuyRequest['options']['ampromo_rule_id'])) {
                    $collection->removeItemByKey($product->getId());
                }
            }

            \Amasty\Promo\Model\Storage::$isReorder = false;
        }

        return $collection;
    }
}
