<?php

namespace Wcb\Store\Model;

use Magento\Checkout\Model\Session;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Registry;
use Magento\Quote\Model\QuoteRepository;
use Wurth\Shippingproduct\Helper\AddRemoveShippingProduct;

class AddStoreToQuote extends AbstractModel
{
    protected $addRemoveShippingProduct;

    protected $registry;

    public function __construct(
        Session $checkoutSession,
        QuoteRepository $quoteRepository,
        AddRemoveShippingProduct $addRemoveShippingProduct,
        Registry $registry
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->addRemoveShippingProduct = $addRemoveShippingProduct;
        $this->registry = $registry;
    }

    public function setStore($store)
    {
        $quote = $this->getQuotes();
        $action = $store['action'];
        $quoteId = $quote->getId();
        $quote = $this->quoteRepository->get($quoteId);

        if ($quote && $action == 1) {
            $quote->setData('pickup_store_id', $store['entity_id']);
            $quote->setData('pickup_store_name', $store['name']);
            $quote->setData('pickup_store_email', $store['contact_email']);
            $quote->setData('pickup_store_address', $store['address']);
            $this->quoteRepository->save($quote);

            $this->registry->register('skip_plugin', 'true');
            $this->addRemoveShippingProduct->removeShippingProduct($quote->getAllVisibleItems());
            $this->registry->unregister('skip_plugin');
            return json_encode($store['address']);
            return '1';
        } else {
            $quote->setData('pickup_store_id', "");
            $quote->setData('pickup_store_name', "");
            $quote->setData('pickup_store_email', "");
            $quote->setData('pickup_store_address', "");
            $this->quoteRepository->save($quote);
            $this->addRemoveShippingProduct->updateShippingProduct($quote);
            return '2';
        }

        return false;
    }

    public function getQuotes()
    {
        return $this->checkoutSession->getQuote();
    }
}
