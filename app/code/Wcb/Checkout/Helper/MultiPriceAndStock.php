<?php

namespace Wcb\Checkout\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Wcb\ApiConnect\Model\SoapClient;

class MultiPriceAndStock extends AbstractHelper
{
    protected $resultJsonFactory;
    protected $soapApiClient;

    public function __construct(
        Context $context,
        SoapClient $soapApiClient,
        JsonFactory $resultJsonFactory
    ) {
        $this->soapApiClient = $soapApiClient;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function getMultiStockAndPriceData($skus, $type)
    {
        $dataString = "";
        $finalData = [];

        $skus = json_decode($skus, true);
        $skus = isset($skus['skus']) ? $skus['skus'] : $skus;

        foreach ($skus as $key => $sku) {
            $dataString .= '"' . $sku['product_code'] . '"' . ';' . '"' . $sku['qty'] . '"' . PHP_EOL;
        }

        $dataString = trim($dataString);

        $result = $this->resultJsonFactory->create();
        if ($type === 'price') {
            $xmlData = $this->getMultiPrice($dataString);
        } else {
            $xmlData = $this->getMultiStock($dataString);
        }

        if ($xmlData) {
            if ($type == 'price') {
                $data = $xmlData->SoapBody->GetMultiItemEShopSalesPriceAndDisc_Result->salesLinesCsvP;
            } else {
                $data = $xmlData->SoapBody->GetMultiItemAvailabilityOnLocation_Result->itemsCsvP;
            }
            $data = (string)$data;

            $data = $this->soapApiClient->csvstring_to_array($data);

            $header = reset($data);
            $header = explode(';', $header[0]);

            foreach ($data as $key => $row) {
                if (empty($row)) {
                    continue;
                }

                if ($key == 1) {
                    $header = $this->soapApiClient->trimMiddleWhiteSpaces($header);
                }
                $dataStage2 = explode(';', $row[0]);

                if (count($header) === count($dataStage2) && $key != 0) {
                    $finalData[] = array_combine($header, $dataStage2);
                }
            }
        }
        return $finalData;
    }

    public function getMultiPrice($skus)
    {
        return $this->soapApiClient->GetMultiItemEShopSalesPriceAndDisc($skus);
    }

    public function getMultiStock($skus)
    {
        return $this->soapApiClient->GetMultiItemAvailabilityOnLocation($skus);
    }
}
