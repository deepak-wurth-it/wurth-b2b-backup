<?php

namespace Amasty\Promo\Helper;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Retrieve specific Cart Item Data
 */
class Item
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return int|null
     */
    public function getRuleId(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        if (!($ruleId = $item->getData('ampromo_rule_id'))) {
            $ruleId = $this->getRuleIdFromBuyRequest($item->getBuyRequest());

            $item->setData('ampromo_rule_id', $ruleId);
        }

        return $ruleId;
    }

    /**
     * @param array|\Magento\Framework\DataObject $buyRequest
     *
     * @return int|null
     */
    public function getRuleIdFromBuyRequest($buyRequest)
    {
        if (isset($buyRequest['options']['ampromo_rule_id'])) {
            return (int)$buyRequest['options']['ampromo_rule_id'];
        }

        return null;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return bool
     */
    public function isPromoItem(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        if ($this->storeManager->getStore()->getCode() == \Magento\Store\Model\Store::ADMIN_CODE) {
            return false;
        }

        return $this->getRuleId($item) !== null;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return mixed
     */
    public function getItemSku(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        $productType = $item->getProductType();
        if ($productType == Configurable::TYPE_CODE) {
            return $item->getProduct()->getData('sku');
        }

        return $item->getSku();
    }
}
