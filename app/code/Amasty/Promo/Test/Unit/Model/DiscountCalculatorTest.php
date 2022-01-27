<?php

namespace Amasty\Promo\Test\Unit\Model;

use Amasty\Promo\Model\Rule;
use Amasty\Promo\Model\DiscountCalculator;
use Amasty\Promo\Test\Unit\Traits;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Quote\Model\Quote\Item;
use Magento\SalesRule\Model\Rule as SalesRule;

/**
 * Class DiscountCalculatorTest
 *
 * @see DiscountCalculator
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class DiscountCalculatorTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const ITEM_PRICE = 99.99;
    const ITEM_QTY = 1;

    /**
     * @covers       DiscountCalculator::getBaseDiscountAmount
     *
     * @dataProvider getDifferentRules
     *
     * @param SalesRule $rule
     * @param float $expected
     */
    public function testGetBaseDiscountAmount($rule, $expected)
    {
        $model = $this->getObjectManager()->getObject(DiscountCalculator::class);

        $this->assertEquals($expected,$model->getBaseDiscountAmount($rule, $this->initQuoteItem()));
    }

    /**
     * @return array
     */
    public function getDifferentRules()
    {
        return [
            [
                $this->initRule(''),
                static::ITEM_PRICE
            ],
            [
                $this->initRule('50%'),
                static::ITEM_PRICE * 0.5
            ],
            [
                $this->initRule('-80'),
                80
            ],
            [
                $this->initRule('80'),
                static::ITEM_PRICE - 80
            ],
            [
                $this->initRule('', 80),
                static::ITEM_PRICE - 80
            ],
            [
                $this->initRule('50%', 80),
                static::ITEM_PRICE - 80
            ],
            [
                $this->initRule('-30', 40),
                30
            ],
        ];
    }

    /**
     * @param float $discount
     * @param float $minimalPrice
     *
     * @return SalesRule|MockObject
     */
    private function initRule($discount, $minimalPrice = 0.0)
    {
        /** @var SalesRule|MockObject $rule */
        $rule = $this->createPartialMock(
            SalesRule::class,
            ['getAmpromoRule']
        );

        /** @var Rule $promoRule */
        $promoRule = $this->getObjectManager()->getObject(Rule::class);
        $promoRule->setItemsDiscount($discount);
        $promoRule->setMinimalItemsPrice($minimalPrice);

        $rule->expects($this->once())->method('getAmpromoRule')->willReturn($promoRule);

        return $rule;
    }

    /**
     * @return Item
     */
    private function initQuoteItem()
    {
        /** @var Item $item */
        $item = $this->getObjectManager()->getObject(Item::class);
        $item->setBasePrice(static::ITEM_PRICE);
        $item->setData(\Magento\Quote\Api\Data\CartItemInterface::KEY_QTY, static::ITEM_QTY);

        return $item;
    }

}
