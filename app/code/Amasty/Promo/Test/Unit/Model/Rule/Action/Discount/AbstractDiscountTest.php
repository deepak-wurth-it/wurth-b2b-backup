<?php

namespace Amasty\Promo\Test\Unit\Model\Rule\Action\Discount;

use Amasty\Promo\Model\Rule\Action\Discount\AbstractDiscount;
use Amasty\Promo\Test\Unit\Traits;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class AbstractDiscountTest
 *
 * @see AbstractDiscount
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class AbstractDiscountTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var AbstractDiscount
     */
    private $model;

    /**
     * @covers AbstractDiscount::getPromoQtyByStep
     *
     * @param int $discountAmount
     * @param int $discountStep
     * @param int $discountQty
     * @param int $itemQty
     * @param float|int $expectedResult
     *
     * @dataProvider getPromoQtyByStepDataProvider
     *
     * @throws \ReflectionException
     */
    public function testGetPromoQtyByStep($discountAmount, $discountStep, $discountQty, $itemQty, $expectedResult = 0.0)
    {
        $this->model = $this->getMockBuilder(AbstractDiscount::class)
            ->disableOriginalConstructor()
            ->getMock();

        $helper = $this->createMock(\Amasty\Promo\Helper\Item::class);
        $helper->expects($this->any())->method('isPromoItem')->willReturn(false);

        $this->setProperty($this->model, 'promoItemHelper', $helper, AbstractDiscount::class);

        $quote = $this->initQuote($itemQty);
        $rule = $this->initRule($discountAmount, $discountStep, $discountQty);
        $item = $this->initItem($quote);

        $result = $this->invokeMethod($this->model, 'getPromoQtyByStep', [$rule, $item]);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Init rule for test
     * @return \Magento\SalesRule\Model\Rule|MockObject
     */
    private function initRule($discountAmount, $discountStep, $discountQty)
    {
        $rule = $this->getMockBuilder(\Magento\SalesRule\Model\Rule::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDiscountAmount', 'getDiscountStep', 'getName', 'getDiscountQty', 'getActions'])
            ->getMock();
        $actions = $this->createPartialMock(\Magento\Rule\Model\Action\Collection::class, ['validate']);
        $actions->expects($this->any())->method('validate')->willReturn(true);

        $rule->expects($this->any())->method('getDiscountAmount')->willReturn($discountAmount);
        $rule->expects($this->any())->method('getDiscountStep')->willReturn($discountStep);
        $rule->expects($this->any())->method('getName')->willReturn('rule_name');
        $rule->expects($this->any())->method('getDiscountQty')->willReturn($discountQty);
        $rule->expects($this->any())->method('getActions')->willReturn($actions);

        return $rule;
    }

    /**
     * Init quote for test
     *
     * @param int $itemQty
     *
     * @return \Magento\Quote\Model\Quote|MockObject
     */
    private function initQuote($itemQty)
    {
        $address = $this->createMock(\Magento\Quote\Model\Quote\Address::class);
        /** @var \Magento\Catalog\Model\Product|MockObject $product */
        $product = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['getParentProductId']);
        $product->expects($this->any())->method('getParentProductId')
            ->willReturn(null);
        /** @var \Magento\Quote\Model\Quote\Item|MockObject $quoteItem */
        $quoteItem = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getProduct', 'getAddress', 'getQty']
        );
        $quoteItem->expects($this->any())->method('getProduct')
            ->willReturn($product);
        $quoteItem->expects($this->any())->method('getAddress')
            ->willReturn($address);
        $quoteItem->expects($this->any())->method('getQty')
            ->willReturn($itemQty);

        /** @var \Magento\Quote\Model\Quote|MockObject $quote */
        $quote = $this->createPartialMock(
            \Magento\Quote\Model\Quote::class,
            ['getAllVisibleItems']
        );
        $quote->expects($this->any())->method('getAllVisibleItems')
            ->willReturn([$quoteItem]);

        return $quote;
    }

    /**
     * Init item for test
     * @return \Magento\Quote\Model\Quote\Item\AbstractItem|MockObject
     */
    private function initItem($quote)
    {
        $item = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item\AbstractItem::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQuote'])
            ->getMockForAbstractClass();
        $item->expects($this->any())->method('getQuote')->willReturn($quote);

        return $item;
    }

    /**
     * Data provider for getPromoQtyByStep test
     * @return array
     */
    public function getPromoQtyByStepDataProvider()
    {
        return [
            [1, 1, 1, 1, 1],
            [1, 5, 10, 1],
            [10, 50, 100, 1],
            [0, 0, 0, 1, 1],
            [0, 0, 0, 5, 5],
            [10, 50, 55, 5],
            [1, 1, 1, 5, 1],
        ];
    }
}
