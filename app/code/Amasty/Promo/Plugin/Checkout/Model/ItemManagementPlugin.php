<?php

namespace Amasty\Promo\Plugin\Checkout\Model;

use Amasty\CheckoutCore\Api\ItemManagementInterface;
use Amasty\Promo\Helper\Item;
use Laminas\Uri\Uri as LaminasUri;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Zend\Uri\Uri as ZendUri;

class ItemManagementPlugin
{
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var Item
     */
    private $helperItem;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var
     */
    private $uri;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        Item $helperItem,
        ObjectManagerInterface $objectManager
    ) {
        $this->cartRepository = $cartRepository;
        $this->helperItem = $helperItem;
        $this->objectManager = $objectManager;

        if (interface_exists(LaminasUri::class)) {
            $this->uri = $this->objectManager->get(LaminasUri::class);
        } else {
            $this->uri = $this->objectManager->get(ZendUri::class);
        }
    }

    /**
     * @param ItemManagementInterface $subject
     * @param int $cartId
     * @param int $itemId
     * @param string $formData
     *
     * @return array
     */
    public function beforeUpdate(
        ItemManagementInterface $subject,
        $cartId,
        $itemId,
        $formData
    ) {
        /** @var Quote $quote */
        $quote = $this->cartRepository->get($cartId);
        $item = $quote->getItemById($itemId);

        if (!$this->helperItem->isPromoItem($item)) {
            return [$cartId, $itemId, $formData];
        }

        $this->uri->setQuery($formData);
        $params = $this->uri->getQueryAsArray();
        $params['options'] = $item->getBuyRequest()->getOptions() ?? [];
        $this->uri->setQuery($params);
        $formData = $this->uri->getQuery();

        return [$cartId, $itemId, $formData];
    }
}
