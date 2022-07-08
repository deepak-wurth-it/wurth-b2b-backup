<?php

namespace Wcb\BarcodeApi\Model;

use Wcb\BarcodeApi\Api\BarCodeManagementInterface;
use Wcb\BarcodeApi\Model\ResourceModel\Barcodes\CollectionFactory;

/**
 * Defines the implementation class of the \Wcb\BarcodeApi\Api\BarCodeManagementInterface
 */
class BarCodeManagement implements BarCodeManagementInterface
{

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get Product Compare Data
     * @param int $bar_code
     * @return array Compare Data
     */
    public function getProductByBarCode($bar_code)
    {
        if (!isset($bar_code) || $bar_code == "") {
            $message = __('Bar Code required');
            $status = false;
            $response[] = [
                "message" => $message,
                "status" => $status
            ];
            return $response;
        } else {
            $barCodeCollection = $this->collectionFactory->create();
            $responseData = $barCodeCollection->addFieldToFilter('Code', ['eq' => $bar_code])
                ->addFieldToFilter('Active', ['eq' => 1])->getFirstItem()->getDataByKey('ProductId');
            $compareData = [];

            $compareData[] = [
                'sku' => $responseData
            ];

            return $compareData;
        }
    }
}
