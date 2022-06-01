<?php

namespace Wcb\Checkout\Plugin;

use Magento\Checkout\Model\DefaultConfigProvider;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Wcb\Checkout\Helper\Data as checkoutHelper;

class AddStockApiData extends AbstractModel
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;
    /**
     * @var checkoutHelper
     */
    protected $checkoutHelper;
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * AddStockApiData constructor.
     * @param CheckoutSession $checkoutSession
     * @param checkoutHelper $checkoutHelper
     * @param RequestInterface $request
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        checkoutHelper $checkoutHelper,
        RequestInterface $request
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->checkoutHelper = $checkoutHelper;
        $this->request = $request;
    }

    /**
     * @param DefaultConfigProvider $subject
     * @param array $result
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterGetConfig(DefaultConfigProvider $subject, array $result)
    {
        if ($this->request->getFullActionName() === 'checkout_index_index') {
            $items = $result['totalsData']['items'];
            foreach ($items as $index => $item) {
                $quoteItem = $this->checkoutSession->getQuote()->getItemById($item['item_id']);
                $stockData = $this->checkoutHelper->getStockApiData($quoteItem->getProduct()->getProductCode(), $quoteItem->getQty());
                $result['quoteItemData'][$index]['stock_data'] = $stockData;
            }
        }
        return $result;
    }
}
