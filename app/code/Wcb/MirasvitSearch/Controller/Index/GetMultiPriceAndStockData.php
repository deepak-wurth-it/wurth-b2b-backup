<?php

namespace Wcb\MirasvitSearch\Controller\Index;

use Exception;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;
use Wcb\Checkout\Helper\MultiPriceAndStock;

class GetMultiPriceAndStockData extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var MultiPriceAndStock
     */
    protected $multiPriceAndStock;

    /**
     * Removeitem constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     * @param ManagerInterface $messageManager
     * @param CustomerCart $cart
     * @param MultiPriceAndStock $multiPriceAndStock
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        ManagerInterface $messageManager,
        CustomerCart $cart,
        MultiPriceAndStock $multiPriceAndStock
    ) {
        $this->logger = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->multiPriceAndStock = $multiPriceAndStock;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $response = $this->resultJsonFactory->create();
        $data = $this->getRequest()->getParam('skus');

        $result = [];
        try {
            $responsePriceData = $this->multiPriceAndStock->getMultiStockAndPriceData($data, 'price');
            //$responseStockData = $this->multiPriceAndStock->getMultiStockAndPriceData($data, 'stock');

            $result['success'] = "true";
            $result['priceData'] = $responsePriceData;
            $result['stockData'] = '';
            $result['message'] = __("Success.");
        } catch (Exception $e) {
            $result['success'] = "false";
            $result['priceData'] = '';
            $result['stockData'] = '';
            $result['message'] = __($e->getMessage());
        }
        $response->setData($result);
        return $response;
    }
}
