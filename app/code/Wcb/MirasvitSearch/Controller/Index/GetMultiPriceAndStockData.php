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
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
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
     * @var TimezoneInterface
     */
    protected $date;

    /**
     * Removeitem constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     * @param ManagerInterface $messageManager
     * @param CustomerCart $cart
     * @param MultiPriceAndStock $multiPriceAndStock
     * @param TimezoneInterface $date
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        ManagerInterface $messageManager,
        CustomerCart $cart,
        MultiPriceAndStock $multiPriceAndStock,
        TimezoneInterface $date
    ) {
        $this->logger = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->multiPriceAndStock = $multiPriceAndStock;
        $this->date = $date;
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
            $responseStockData = $this->multiPriceAndStock->getMultiStockAndPriceData($data, 'stock');
            $todayDate = $this->date->date()->format('Y-m-d');

            $result['success'] = "true";
            $result['priceData'] = $responsePriceData;
            $result['stockData'] = $responseStockData;
            $result['currentDate'] = $todayDate;
            $result['message'] = __("Success.");
        } catch (Exception $e) {
            $result['success'] = "false";
            $result['priceData'] = '';
            $result['stockData'] = '';
            $result['currentDate'] = '';
            $result['message'] = __($e->getMessage());
        }
        $response->setData($result);
        return $response;
    }
}
