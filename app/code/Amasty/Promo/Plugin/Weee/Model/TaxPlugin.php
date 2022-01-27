<?php

namespace Amasty\Promo\Plugin\Weee\Model;

use Amasty\Promo\Model\ResourceModel\Rule;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Weee\Model\Tax;

class TaxPlugin
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    private $rule;

    public function __construct(
        Session $checkoutSession,
        CartRepositoryInterface $quoteRepository,
        Rule $rule
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->rule = $rule;
    }

    /**
     * @param Tax $subject
     * @param \Magento\Framework\DataObject[] $result
     * @param Product $product
     * @param null $shipping
     * @param null $billing
     * @param null $website
     * @param null $calculateTax
     * @param bool $round
     * @return array
     */
    public function afterGetProductWeeeAttributes(
        Tax $subject,
        $result,
        $product,
        $shipping = null,
        $billing = null,
        $website = null,
        $calculateTax = null,
        $round = true
    ) {
        try {
            $quote = $this->quoteRepository->get($this->checkoutSession->getQuoteId());

            $applyTax = false;
            $isFree = false;
            $productIds = [];
            foreach ($quote->getAllItems() as $item) {
                if ($item->getProductId() == $product->getId() && $item->getAmpromoRuleId() !== null) {
                    $applyTax =  $this->rule->isApplyTax($item->getAmpromoRuleId());
                    $isFree = true;
                } else {
                    $productIds[] = $item->getProductId();
                }
            }

            if ($isFree && !$applyTax && !in_array($item->getProductId(), $productIds)) {
                $result = [];
            }
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
        } catch (NoSuchEntityException $e) {
            // no quota to check
        }

        return $result;
    }
}
