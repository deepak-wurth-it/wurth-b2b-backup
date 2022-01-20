<?php

namespace Amasty\Promo\Plugin\Tax\Model\TaxDetails;

use Amasty\Promo\Model\Storage;
use Magento\Tax\Model\TaxDetails\TaxDetails as TaxDetailsModel;

class TaxDetails
{

    public function __construct(
        \Amasty\Promo\Helper\Item $helperItem,
        \Amasty\Promo\Model\ResourceModel\Rule $ruleResource,
        \Magento\Checkout\Model\Session $resourceSession
    ) {
        $this->checkoutSession = $resourceSession;
        $this->helperItem = $helperItem;
        $this->ruleResource = $ruleResource;
    }

    /**
     * @param TaxDetailsModel $subject
     * @param TaxDetailsModel $result
     * @return mixed
     */
    public function afterSetItems(TaxDetailsModel $subject, $result)
    {
        if (isset($result->getData()['items'])) {
            foreach ($result->getData()['items'] as $key => &$value) {
                if (array_key_exists($key, Storage::$cachedFreeGiftsWithTax)) {
                    $value->setPriceInclTax($value->getPriceInclTax() - $value->getPrice());
                    $value->setPrice(0);
                    $result->setSubtotal($result->getSubtotal() - $value->getRowTotal());
                    $value->setRowTotalInclTax($value->getRowTotalInclTax() - $value->getRowTotal());
                    $value->setRowTotal(0);

                    Storage::$cachedQuoteItemPricesWithTax[Storage::$cachedFreeGiftsWithTax[$key]]['price_incl_tax']
                        = $value->getPriceInclTax();
                    Storage::$cachedQuoteItemPricesWithTax[Storage::$cachedFreeGiftsWithTax[$key]]['row_total_incl_tax']
                        = $value->getRowTotalInclTax();
                }
            }
        }

        return $result;
    }
}
