<?php
declare(strict_types=1);

namespace Amasty\Promo\Model\Order\Creditmemo\Item;

use Magento\Framework\App\RequestInterface;
use Magento\Sales\Model\Order\Creditmemo\Item;

class Checker
{
    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * @param Item $item
     * @return bool
     */
    public function isParentItemToRefund(Item $item): bool
    {
        $orderItem = $item->getOrderItem();
        $creditmemoData = $this->request->getParam('creditmemo', ['items' => null]);
        $qtys = $creditmemoData['items'];
        $isParentItemToRefund = false;

        if (!empty($qtys)) {
            $childItems = (array)$orderItem->getChildrenItems();

            foreach ($childItems as $childItem) {
                $itemQtyToRefund = $qtys[$childItem->getItemId()]['qty'] ?? 0;
                if ($itemQtyToRefund) {
                    $isParentItemToRefund = true;
                    break;
                }
            }
        }

        return $isParentItemToRefund;
    }
}
