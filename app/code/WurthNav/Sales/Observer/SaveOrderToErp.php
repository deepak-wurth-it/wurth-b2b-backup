<?php


namespace WurthNav\Sales\Observer;

use Magento\Framework\Event\ObserverInterface;

class SaveOrderToErp implements ObserverInterface
{
	/**
     * Order Model
     *
     * @var \Magento\Sales\Model\Order $order
     */
    protected $order;
    /**
     *
     * @var Magento\Catalog\Model\ProductFactory $_productloader
     */
    protected $_productloader;
    /**
     *
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Framework\DB\Transaction $transaction
     * @param \Magento\Catalog\Model\ProductFactory $_productloader
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->order = $order;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->logger = $logger;
        $this->_productloader = $_productloader;
        $this->scopeConfig = $scopeConfig;
    }

	/*
	 * Generate Auto Invoice once successfully order is place by customer in a frontend.
	 *
	 * */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderId = $observer->getEvent()->getOrderIds();
        $order = $this->order->load($orderId);

        if($order->canInvoice()) {

        }


    }
    /**
     *
     * @param type $id
     * @return type
     */
    public function getLoadProduct($id)
    {
        return $this->_productloader->create()->load($id);
    }
}
