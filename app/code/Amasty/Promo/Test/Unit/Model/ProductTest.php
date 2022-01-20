<?php

namespace Amasty\Promo\Test\Unit\Model;

use Amasty\Promo\Model\Product;
use Amasty\Promo\Test\Unit\Traits;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @see Product
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class ProductTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const STORE_ID = 1;
    const WEBSITE_ID = 2;

    const STOCK_QTY = 10000;
    const MAX_QTY = 999;
    const PRODUCT_SKU = 'SKU';

    /**
     * @covers Product::getProductQty
     *
     * @dataProvider dataProviderForGetProductQtyReturnsFalse
     *
     * @param string $productType
     */
    public function testGetProductQtyReturnsFalse($productType) {
        $getProductTypesBySkus = $this->getProductTypeBySku($productType, self::PRODUCT_SKU);

        $model = $this->getObjectManager()->getObject(
            Product::class,
            [
                'getProductTypesBySkus' => $getProductTypesBySkus
            ]
        );
        $result = $model->getProductQty(self::PRODUCT_SKU);

        $this->assertFalse($result);
    }

    /**
     * @covers Product::getProductQty
     */
    public function testGetProductQtyOutOfStock() {
        $getProductTypesBySkus = $this->getProductTypeBySku(
            \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
            self::PRODUCT_SKU
        );

        $stockStatus = $this->createMock(\Magento\CatalogInventory\Model\Stock\Status::class);
        $stockStatus->expects($this->once())->method('getStockStatus')->willReturn(false);

        $stockRegistry = $this->createMock(\Magento\CatalogInventory\Api\StockRegistryInterface::class);
        $stockRegistry->expects($this->once())->method('getStockItemBySku')->willReturn($stockStatus);
        $stockRegistry->expects($this->once())->method('getStockStatusBySku')->willReturn($stockStatus);

        $model = $this->getObjectManager()->getObject(
            Product::class,
            [
                'storeManager' => $this->getStoreManager(),
                'getProductTypesBySkus' => $getProductTypesBySkus,
                'stockRegistry' => $stockRegistry
            ]
        );
        $result = $model->getProductQty(self::PRODUCT_SKU);

        $this->assertTrue($result === 0);
    }

    /**
     * @covers Product::getProductQty
     *
     * @dataProvider dataProviderForGetProductQty
     *
     * @param int $expected
     * @param bool $manageStock
     * @param bool $backorders
     * @param int $maxSaleQty
     * @param int $stockStatusQty
     */
    public function testGetProductQty(
        $expected,
        $manageStock,
        $backorders,
        $maxSaleQty,
        $stockStatusQty
    ) {
        $getProductTypesBySkus = $this->getProductTypeBySku(
            \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
            self::PRODUCT_SKU
        );

        $stockStatus = $this->createMock(\Magento\CatalogInventory\Model\Stock\Status::class);
        $stockStatus->expects($this->any())->method('getStockStatus')->willReturn(true);
        $stockStatus->expects($this->any())->method('getQty')->willReturn($stockStatusQty);

        $stockItem = $this->createMock(\Magento\CatalogInventory\Api\Data\StockItemInterface::class);
        $stockItem->expects($this->any())->method('getManageStock')->willReturn($manageStock);
        $stockItem->expects($this->any())->method('getBackorders')->willReturn($backorders);
        $stockItem->expects($this->any())->method('getMaxSaleQty')->willReturn($maxSaleQty);

        $stockRegistry = $this->createMock(\Magento\CatalogInventory\Api\StockRegistryInterface::class);
        $stockRegistry->expects($this->once())->method('getStockItemBySku')->with(
            self::PRODUCT_SKU,
            $this->getStoreManager()->getWebsite()->getId()
        )->willReturn($stockItem);
        $stockRegistry->expects($this->once())->method('getStockStatusBySku')->willReturn($stockStatus);

        $model = $this->getObjectManager()->getObject(
            Product::class,
            [
                'storeManager' => $this->getStoreManager(),
                'getProductTypesBySkus' => $getProductTypesBySkus,
                'stockRegistry' => $stockRegistry
            ]
        );
        $result = $model->getProductQty(self::PRODUCT_SKU);

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers Product::getProductQty
     *
     * @dataProvider dataProviderForCheckAvailableQtyWithNoGetProductQty
     *
     * @param int $expected
     * @param mixed $getProductQtyValue
     * @param int $qtyRequested
     */
    public function testCheckAvailableQtyWithNoGetProductQty($expected, $getProductQtyValue, $qtyRequested)
    {
        $productMock = $this->createPartialMock(Product::class, ['getProductQty']);
        $productMock->expects($this->once())->method('getProductQty')->willReturn($getProductQtyValue);

        $result = $productMock->checkAvailableQty(self::PRODUCT_SKU, $qtyRequested);
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers       Product::getProductQty
     *
     * @dataProvider dataProviderForCheckAvailableQty
     *
     * @param int $expected
     * @param mixed $getProductQtyValue
     * @param int $qtyRequested
     * @param array $itemsArray
     */
    public function testCheckAvailableQty($expected, $getProductQtyValue, $qtyRequested, $itemsArray)
    {
        $items = $this->prepareQuoteItems($itemsArray);
        $quote = $this->createMock(\Magento\Quote\Model\Quote::class);
        $quote->expects($this->once())->method('getAllVisibleItems')->willReturn($items);

        $checkoutSession = $this->createMock(\Magento\Checkout\Model\Session::class);
        $checkoutSession->expects($this->once())->method('getQuote')->willReturn($quote);

        $constrArgs = $this->getObjectManager()->getConstructArguments(Product::class);
        $constrArgs['checkoutSession'] = $checkoutSession;

        $productMock = $this->getMockBuilder(Product::class)
            ->setConstructorArgs($constrArgs)
            ->setMethods(['getProductQty'])
            ->getMock();

        $productMock->expects($this->once())->method('getProductQty')->willReturn($getProductQtyValue);

        $result = $productMock->checkAvailableQty(self::PRODUCT_SKU, $qtyRequested);
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers       Product::getProductQty
     *
     * @dataProvider dataProviderForCheckAvailableQty
     *
     * @param int $expected
     * @param mixed $getProductQtyValue
     * @param int $qtyRequested
     * @param array $itemsArray
     */
    public function testCheckAvailableQtyWithQuote($expected, $getProductQtyValue, $qtyRequested, $itemsArray)
    {
        $items = $this->prepareQuoteItems($itemsArray);
        $quote = $this->createMock(\Magento\Quote\Model\Quote::class);
        $quote->expects($this->once())->method('getAllVisibleItems')->willReturn($items);

        $checkoutSession = $this->createMock(\Magento\Checkout\Model\Session::class);
        $checkoutSession->expects($this->never())->method('getQuote')->willReturn($quote);

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setConstructorArgs(['checkoutSession' => $checkoutSession])
            ->setMethods(['getProductQty'])
            ->getMock();

        $productMock->expects($this->once())->method('getProductQty')->willReturn($getProductQtyValue);

        $result = $productMock->checkAvailableQty(self::PRODUCT_SKU, $qtyRequested, $quote);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function dataProviderForGetProductQtyReturnsFalse()
    {
        return [
            [
                \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
            ],
            [
                \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
            ],
            [
                \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL
            ],
        ];
    }

    /**
     * @return array
     */
    public function dataProviderForGetProductQty()
    {
        return [
            [
                4,
                false,
                false,
                4,
                3
            ],
            [
                4,
                true,
                true,
                4,
                3
            ],

            [
                4,
                false,
                true,
                4,
                3
            ],

            [
                2,
                true,
                false,
                4,
                2
            ]
        ];
    }

    /**
     * @return array
     */
    public function dataProviderForCheckAvailableQtyWithNoGetProductQty()
    {
        return [
            [
                3,
                false,
                3
            ],
            [
                0,
                null,
                3
            ],
            [
                0,
                0,
                3
            ],
        ];
    }

    /**
     * @return array
     */
    public function dataProviderForCheckAvailableQty()
    {
        return [
            [
                3,
                9999,
                3,
                [
                    [
                        'sku' => self::PRODUCT_SKU,
                        'qty' => 3
                    ]
                ]
            ],
            [
                1,
                4,
                4,
                [
                    [
                        'sku' => self::PRODUCT_SKU,
                        'qty' => 2
                    ],
                    [
                        'sku' => self::PRODUCT_SKU,
                        'qty' => 1
                    ],
                    [
                        'sku' => self::PRODUCT_SKU . 'asdasdasd',
                        'qty' => 4
                    ]
                ]
            ],

            [
                0,
                6,
                4,
                [
                    [
                        'sku' => self::PRODUCT_SKU,
                        'qty' => 7
                    ],
                    [
                        'sku' => self::PRODUCT_SKU . 'asdasdasd',
                        'qty' => 4
                    ]
                ]
            ],
        ];
    }

    /**
     * @return \Magento\Store\Model\StoreManager|MockObject
     */
    private function getStoreManager()
    {
        /** @var \Magento\Store\Model\Store|MockObject $store */
        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getCode', 'getId', 'getWebsiteId']);
        $store->expects($this->any())->method('getId')->willReturn(self::STORE_ID);
        $store->expects($this->any())->method('getCode')->willReturn('default');
        $store->expects($this->any())->method('getWebsiteId')->willReturn(self::WEBSITE_ID);

        /** @var \Magento\Store\Model\Website|MockObject $website */
        $website = $this->createPartialMock(\Magento\Store\Model\Website::class, ['getId']);
        $website->expects($this->any())->method('getId')->willReturn(static::WEBSITE_ID);

        /** @var \Magento\Store\Model\StoreManager|MockObject $storeManager */
        $storeManager = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $storeManager->expects($this->any())->method('getWebsite')->willReturn($website);

        return $storeManager;
    }

    /**
     * @param string $productType
     * @param string $sku
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getProductTypeBySku($productType, $sku)
    {
        $getProductTypesBySkus = $this->createMock(\Amasty\Promo\Model\ResourceModel\GetProductTypesBySkus::class);
        $getProductTypesBySkus->expects($this->once())->method('execute')->willReturn([$sku => $productType]);

        return $getProductTypesBySkus;
    }

    /**
     * @param array $itemsArray
     * @return array
     */
    private function prepareQuoteItems($itemsArray)
    {
        $items = [];
        foreach ($itemsArray as $itemData) {
            $item = $this->createMock(\Magento\Quote\Model\Quote\Item::class);
            $item->expects($this->any())->method('getSku')->willReturn($itemData['sku']);
            $item->expects($this->any())->method('hasData')->willReturn(false);
            $item->expects($this->any())->method('getQty')->willReturn($itemData['qty']);
            $items[] = $item;
        }

        return $items;
    }
}
