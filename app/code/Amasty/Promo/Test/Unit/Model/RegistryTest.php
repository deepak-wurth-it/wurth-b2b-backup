<?php

namespace Amasty\Promo\Test\Unit\Model;

use Amasty\Promo\Model\Registry;
use Amasty\Promo\Test\Unit\Traits;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class RegistryTest
 *
 * @see Registry
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class RegistryTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const INTEGER_DISCOUNT_DATA = [
        'discount_item' => 10,
        'minimal_price' => 2,
    ];
    const EMPTY_DISCOUNT_DATA = [
        'discount_item' => '',
        'minimal_price' => '',
    ];
    const STRING_DISCOUNT_DATA = [
        'discount_item' => 'test1',
        'minimal_price' => 'test2',
    ];
    const INT_AND_STRING_DISCOUNT_DATA = [
        'discount_item' => '1test',
        'minimal_price' => '2test',
    ];
    const NEGATIVE_DISCOUNT_DATA = [
        'discount_item' => -10,
        'minimal_price' => -2,
    ];

    /**
     * @covers Registry::getCurrencyDiscount
     *
     * @dataProvider getCurrencyDiscountDataProvider
     *
     * @throws \ReflectionException
     */
    public function testGetCurrencyDiscount($value, $expectedResult)
    {
        $model = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $store->expects($this->any())->method('getCurrentCurrencyRate')->willReturn(1);

        $this->setProperty($model, 'store', $store, Registry::class);
        $result = $this->invokeMethod($model, 'getCurrencyDiscount', [$value]);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @covers Registry::deleteProduct
     *
     * @throws \ReflectionException
     */
    public function testDeleteProduct()
    {
        $checkoutSession = $this->createPartialMock(\Magento\Checkout\Model\Session::class, [
            'getAmpromoFullDiscountItems',
            'setAmpromoFullDiscountItems'
        ]);
        $checkoutSession->expects($this->any())->method('getAmpromoFullDiscountItems')->willReturn(1);

        $promoItemData = $this->createMock(\Amasty\Promo\Model\ItemRegistry\PromoItemData::class);
        $promoItem = $this->createPartialMock(\Amasty\Promo\Model\ItemRegistry\PromoItemRegistry::class, [
            'getItemBySkuAndRuleId'
        ]);
        $promoItem->expects($this->any())->method('getItemBySkuAndRuleId')->willReturn($promoItemData);

        $helper = $this->createMock(\Amasty\Promo\Helper\Item::class);
        $helper->expects($this->any())->method('getRuleId')->willReturn(1);

        /** @var Registry $model */
        $model = $this->getObjectManager()->getObject(Registry::class, [
            'checkoutSession' => $checkoutSession,
            'promoItemRegistry' => $promoItem,
            'promoItemHelper' => $helper
        ]);

        /** @var \Magento\Catalog\Model\Product|MockObject $product */
        $product = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['getSku']);
        $product->expects($this->any())->method('getSku')->willReturn('test');

        $item = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getProduct', 'setReservedQty', 'isDeleted']
        );
        $item->expects($this->any())->method('getProduct')->willReturn($product);

        $model->deleteProduct($item);
        $checkoutSession->expects($this->never())->method('setAmpromoFullDiscountItems');
    }

    /**
     * Data provider for afterGet test
     * @return array
     */
    public function getCurrencyDiscountDataProvider()
    {
        return [
            [self::INTEGER_DISCOUNT_DATA, self::INTEGER_DISCOUNT_DATA],
            [self::EMPTY_DISCOUNT_DATA, self::EMPTY_DISCOUNT_DATA],
            [self::STRING_DISCOUNT_DATA, self::STRING_DISCOUNT_DATA],
            [self::INT_AND_STRING_DISCOUNT_DATA, self::INT_AND_STRING_DISCOUNT_DATA],
            [self::NEGATIVE_DISCOUNT_DATA, self::NEGATIVE_DISCOUNT_DATA]
        ];
    }
}
