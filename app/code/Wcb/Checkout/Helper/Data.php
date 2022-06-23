<?php

namespace Wcb\Checkout\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Data extends AbstractHelper
{
    const WEB_DELIVERY_CODE = "SW01";
    const APP_DELIVERY_CODE = "SW04";
    const WEB_CLICK_COLLECT_CODE = "SW06";
    const APP_CLICK_COLLECT_CODE = "SW07";
    const WEB_DELIVERY_LOCATION_CODE = "100";

    protected $productLoader;

    protected $connection;

    protected $productRepository;

    protected $stockApiData;

    protected $registry;

    protected $date;

    protected $priceCurrency;

    protected $multiPriceAndStock;

    protected $checkoutSession;

    protected $type = ['2' => 100];

    public function __construct(
        ProductRepositoryInterface $productrepositoryInterface,
        ProductFactory $productFactory,
        ResourceConnection $resourceConnection,
        Registry $registry,
        TimezoneInterface $date,
        MultiPriceAndStock $multiPriceAndStock,
        PriceCurrencyInterface $priceCurrency,
        Session $checkoutSession,
        Context $context
    ) {
        $this->productLoader = $productFactory;
        $this->productRepository = $productrepositoryInterface;
        $this->connection = $resourceConnection->getConnection();
        $this->registry = $registry;
        $this->date = $date;
        $this->multiPriceAndStock = $multiPriceAndStock;
        $this->priceCurrency = $priceCurrency;
        $this->checkoutSession = $checkoutSession;
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

        if ($stockData) {
            $stockApiResponse = $stockData;
        } else {
            $stockApiResponse = $this->getStockApiResponse();
            $this->registry->unregister('stock_data');
            $this->registry->register('stock_data', $stockApiResponse);
        }

        return $this->getStockDaysAndColor($stockApiResponse, $productCode, $qty);
    }

    public function getStockApiResponse()
    {
        $skus = $this->getItemSkus();
        $responseData = $this->multiPriceAndStock->getMultiStockAndPriceData($skus, 'stock');

        if ($responseData && !empty($responseData)) {
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

    public function getItemSkus($quotes='')
    {
        $quote = ($quotes) ? $quotes : $this->checkoutSession->getQuote();
        $items = $quote->getAllVisibleItems();
        $returnData = [];
        $returnData['skus'][] = [
            "product_code" => 250,
            "qty" => 1
        ];
        foreach ($items as $item) {
            $skuArray = [];
            $skuArray['product_code'] = $item->getProduct()->getProductCode();
            $skuArray['qty'] = $item->getQty();
            $returnData['skus'][] = $skuArray;
        }

        $resultData = '';
        if (!empty($returnData)) {
            $resultData = json_encode($returnData);
        }
        return $resultData;
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
                    //$returnData['showDisplayDays'] = false;
                }
                if ($qty == $availabelQty) {
                    $returnData['color'] = "yellow";
                    //$returnData['showDisplayDays'] = false;
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

    public function getPriceApiData($productCode, $quote='')
    {
        $priceData = $this->registry->registry('price_data');

        if ($priceData) {
            $priceApiResponse = $priceData;
        } else {
            $priceApiResponse = $this->getPriceApiResponse($quote);
            $this->registry->unregister('price_data');
            $this->registry->register('price_data', $priceApiResponse);
        }

        return $this->getPriceAndDiscount($priceApiResponse, $productCode);
    }

    public function getPriceApiResponse($quote)
    {
        $skus = $this->getItemSkus($quote);
        $responsePriceData = $this->multiPriceAndStock->getMultiStockAndPriceData($skus, 'price');
        if ($responsePriceData) {
            $responsePriceItems = json_decode($responsePriceData, true);
            $newResponseData = [];
            foreach ($responsePriceItems as $responsePriceItem) {
                if (isset($responsePriceItem['ItemNo'])) {
                    $newResponseData[$responsePriceItem['ItemNo']] = $responsePriceItem;
                }
            }
            return $newResponseData;
        }
        return [];
    }

    public function getPriceAndDiscount($priceData, $productCode)
    {
        $returnData = [];

        if (isset($priceData[$productCode])) {
            $price = isset($priceData[$productCode]['SuggestedPrice']) ?
                (float)str_replace(',', ".", $priceData[$productCode]['SuggestedPrice']) : 0;
            $discount = isset($priceData[$productCode]['SuggestedDiscount']) ?
                $priceData[$productCode]['SuggestedDiscount'] : 0;
            $discountPrice = isset($priceData[$productCode]['SuggestedPriceInclDiscount']) ?
                (float)str_replace(',', ".", $priceData[$productCode]['SuggestedPriceInclDiscount']) : 0;

            $returnData['price'] = $price;
            $returnData['discount'] = $discount;
            $returnData['discount_price'] = $discountPrice;
            $returnData['discount_amount'] = $price - $discountPrice;
        }
        return $returnData;
    }

    public function getFormattedPrice($price)
    {
        return $this->priceCurrency->format($price, true, 2);
    }
}
