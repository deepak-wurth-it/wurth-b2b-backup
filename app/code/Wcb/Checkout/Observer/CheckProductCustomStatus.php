<?php

namespace Wcb\Checkout\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\SessionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Wcb\Checkout\Helper\ManageProductStatus;

class CheckProductCustomStatus implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var SessionFactory
     */
    protected $checkoutSession;
    /**
     * @var ManagerInterface
     */
    protected $messageManager;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var ManageProductStatus
     */
    protected $manageProductStatus;

    /**
     * CheckProductCustomStatus constructor.
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     * @param SessionFactory $checkoutSession
     * @param ProductRepositoryInterface $productrepositoryInterface
     * @param ManageProductStatus $manageProductStatus
     */
    public function __construct(
        RequestInterface $request,
        ManagerInterface $messageManager,
        SessionFactory $checkoutSession,
        ProductRepositoryInterface $productrepositoryInterface,
        ManageProductStatus $manageProductStatus
    ) {
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
        $this->manageProductStatus = $manageProductStatus;
        $this->productRepository = $productrepositoryInterface;
    }

    /**
     * @param Observer $observer
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $productId = $observer->getRequest()->getParam('product');
        $qty = $observer->getRequest()->getParam('qty');

        $product = $this->productRepository->getById($productId);
        $result = $this->manageProductStatus->checkDiscontinuedProductStatus($product, $qty);
        if (!$result['allow_add_to_cart']) {
            $observer->getRequest()->setParam('product', false);
            $observer->getRequest()->setParam('return_url', false);
        }
    }
}
