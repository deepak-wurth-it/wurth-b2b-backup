<?php

namespace Wcb\Checkout\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Data extends AbstractHelper
{
    protected $productLoader;

    protected $connection;

    protected $productRepository;

    protected $stockApiData;

    protected $registry;

    protected $date;

    protected $type = ['2' => '100'];

    public function __construct(
        ProductRepositoryInterface $productrepositoryInterface,
        ProductFactory $productFactory,
        ResourceConnection $resourceConnection,
        Registry $registry,
        TimezoneInterface $date,
        Context $context
    ) {
        $this->productLoader = $productFactory;
        $this->productRepository = $productrepositoryInterface;
        $this->connection = $resourceConnection->getConnection();
        $this->registry = $registry;
        $this->date = $date;
        parent::__construct($context);
    }

    public function getLoadProduct($id)
    {
        return $this->productRepository->getById($id);
    }

    public function getType($base_unit_of_measure_id)
    {
        $id = (int)$base_unit_of_measure_id;
        $selectExist = $this->connection->select()
            ->from(
                ['uom' => 'unitsofmeasure'],
                ['Code']
            )
            ->where('unitsofmeasure_id = ?', $id);

        return $this->connection->fetchOne($selectExist);
    }

    public function getQuantityUnitByQuantity($qty, $product)
    {
        $qty = (float)$qty;
        $unitOfMeasureId = (float)$product->getBaseUnitOfMeasureId();
        $minimumQty = (float)$product->getMinimumSalesUnitQuantity();
        $unitQty = 1;
        if ($unitOfMeasureId && $minimumQty && $qty) {
            $unitOfMeasure = isset($this->type[$unitOfMeasureId]) ? $this->type[$unitOfMeasureId] : 1;
            $unitQty = $qty / ($unitOfMeasure * $minimumQty);
        }
        return $unitQty;
    }

    public function getTotalQty($product, $qty)
    {
        return $this->getMinimumAndMeasureQty($product) * $qty;
    }

    public function getMinimumAndMeasureQty($product)
    {
        $minimumQty = (float)$product->getMinimumSalesUnitQuantity();
        $unitOfMeasureId = (float)$product->getBaseUnitOfMeasureId();
        $result = 0;
        if ($unitOfMeasureId && $minimumQty) {
            $unitOfMeasure = isset($this->type[$unitOfMeasureId]) ? $this->type[$unitOfMeasureId] : 1;
            $result = $minimumQty * $unitOfMeasure;
        }
        return $result;
    }

    public function getStockApiData($productCode, $qty)
    {
        $stockData = $this->registry->registry('stock_data');
        $data = [];
        if ($stockData) {
            $stockApiResponse = $stockData;
        } else {
            $stockApiResponse = $this->getStockApiResponse();
            $this->registry->register('stock_data', $stockApiResponse);
        }

        $data = $this->getStockDaysAndColor($stockApiResponse, $productCode, $qty);

        return $data;
    }

    public function getStockApiResponse()
    {
        $responseData = "[{\"ItemNo\":\"039 58\",\"AvailableQuantity\":\"500\",\"AvailabilityStatus\":\"1\",\"AvailableonDate\":\"01.06.2022.\"},{\"ItemNo\":\"039 410\",\"AvailableQuantity\":\"500\",\"AvailabilityStatus\":\"3\",\"AvailableonDate\":\"05.06.2022.\"},{\"ItemNo\":\"039 68\",\"AvailableQuantity\":\"0\",\"AvailabilityStatus\":\"3\",\"AvailableonDate\":\"08.04.2022.\"}]";
        if ($responseData) {
            $responseItems = json_decode($responseData, true);
            $newResponseData = [];
            foreach ($responseItems as $responseItem) {
                if (isset($responseItem['ItemNo'])) {
                    $newResponseData[$responseItem['ItemNo']] = $responseItem;
                }
            }
            return $newResponseData;
        }
        return [];
    }

    public function getStockDaysAndColor($stockData, $productCode, $qty)
    {
        $returnData = [];

        if (isset($stockData[$productCode])) {
            if (isset($stockData[$productCode]['AvailableQuantity'])
                && isset($stockData[$productCode]['AvailableonDate'])) {
                $availabelQty = $stockData[$productCode]['AvailableQuantity'];
                //get different days between two dates
                $todayDate = $this->date->date()->format('Y-m-d');
                $availableOnDate = $this->date->date($stockData[$productCode]['AvailableonDate'])->format('Y-m-d');
                $dayLen = 60 * 60 * 24;

                $returnData['stockDays'] = (strtotime($availableOnDate) - strtotime($todayDate)) / $dayLen;
                //Get color using qty
                if ($qty < $availabelQty) {
                    $returnData['color'] = "green";
                    $returnData['showDisplayDays'] = false;
                }
                if ($qty == $availabelQty) {
                    $returnData['color'] = "yellow";
                    $returnData['showDisplayDays'] = false;
                }
                if ($qty > $availabelQty) {
                    $returnData['color'] = "blue";
                    $returnData['showDisplayDays'] = true;
                }
                if ($availabelQty == 0) {
                    $returnData['color'] = "red";
                    $returnData['showDisplayDays'] = true;
                }
            }
        }
        return $returnData;
    }
}
