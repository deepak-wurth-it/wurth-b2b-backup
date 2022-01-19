<?php

namespace Amasty\Promo\Test\Unit\Helper;

use Amasty\Promo\Helper\Data;
use Amasty\Promo\Test\Unit\Traits;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @see Data
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class DataTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @covers       Data::getPromoItemsDataArray
     * @dataProvider prepareData
     *
     * @param array $itemsData
     * @param array $expected
     * @param int $fixQtyDiff
     *
     * @throws \ReflectionException
     */
    public function testGetPromoItemsDataArray($itemsData, $expected, $fixQtyDiff)
    {
        $promoRegistry = $this->createMock(\Amasty\Promo\Model\Registry::class);
        $promoRegistry->expects($this->any())->method('updatePromoItemsReservedQty');

        $product = $this->createMock(\Amasty\Promo\Model\Product::class);

        $items = [];
        foreach ($itemsData as $itemData) {
            $items[] = $this->initItem($itemData);

        }

        $product->expects($this->any())
            ->method('checkAvailableQty')
            ->willReturnCallback(
                function ($sku, $qty) use ($fixQtyDiff) {
                    return $qty-$fixQtyDiff;
                }
            );

        $model = $this->getObjectManager()->getObject(
            Data::class,
            [
                'promoRegistry' => $promoRegistry,
                'promoItemRegistry' => $this->initPromoItemRegistry($items),
                'product' => $product
            ]
        );

        $result = $model->getPromoItemsDataArray();

        $this->assertArrayHasKey('common_qty', $result);
        $this->assertArrayHasKey('triggered_products', $result);
        $this->assertArrayHasKey('promo_sku', $result);
        $this->assertEquals($expected['common_qty'], $result['common_qty']);
    }

    /**
     * @return array
     */
    public function prepareData()
    {
        return [
            [
                [
                    [
                        'rule_id' => 1,
                        'sku' => 'ASQ-1',
                        'qty' => 4,
                        'rule_type' => \Amasty\Promo\Model\Rule::RULE_TYPE_ONE,
                        'minimal_price' => 13
                    ],
                    [
                        'rule_id' => 1,
                        'sku' => 'ASQ-2',
                        'qty' => 4,
                        'rule_type' => \Amasty\Promo\Model\Rule::RULE_TYPE_ONE,
                        'minimal_price' => 10
                    ],
                    [
                        'rule_id' => 1,
                        'sku' => 'ASQ-3',
                        'qty' => 4,
                        'rule_type' => \Amasty\Promo\Model\Rule::RULE_TYPE_ONE,
                        'minimal_price' => 15
                    ],
                    [
                        'rule_id' => 2,
                        'sku' => 'ASQ-4',
                        'qty' => 4,
                        'rule_type' => \Amasty\Promo\Model\Rule::RULE_TYPE_ONE,
                        'minimal_price' => 11
                    ]
                ],
                [
                    'common_qty' => 8
                ],
                0
            ],
            [
                [
                    [
                        'rule_id' => 1,
                        'sku' => 'ASQ1-1',
                        'qty' => 4,
                        'rule_type' => \Amasty\Promo\Model\Rule::RULE_TYPE_ALL,
                        'minimal_price' => 13,
                    ],
                    [
                        'rule_id' => 1,
                        'sku' => 'ASQ1-2',
                        'qty' => 5,
                        'rule_type' => \Amasty\Promo\Model\Rule::RULE_TYPE_ALL,
                        'minimal_price' => 10,
                    ],
                    [
                        'rule_id' => 1,
                        'sku' => 'ASQ1-3',
                        'qty' => 6,
                        'rule_type' => \Amasty\Promo\Model\Rule::RULE_TYPE_ALL,
                        'minimal_price' => 15
                    ],
                    [
                        'rule_id' => 1,
                        'sku' => 'ASQ1-4',
                        'qty' => 7,
                        'rule_type' => \Amasty\Promo\Model\Rule::RULE_TYPE_ALL,
                        'minimal_price' => 0
                    ]
                ],
                [
                    'common_qty' => 22
                ],
                0
            ],
            [
                [
                    [
                        'rule_id' => 1,
                        'sku' => 'ASQ1-1',
                        'qty' => 3,
                        'rule_type' => \Amasty\Promo\Model\Rule::RULE_TYPE_ONE,
                        'minimal_price' => 13,
                    ],
                    [
                        'rule_id' => 1,
                        'sku' => 'ASQ1-2',
                        'qty' => 3,
                        'rule_type' => \Amasty\Promo\Model\Rule::RULE_TYPE_ONE,
                        'minimal_price' => 10,
                    ],
                    [
                        'rule_id' => 2,
                        'sku' => 'ASQ1-3',
                        'qty' => 5,
                        'rule_type' => \Amasty\Promo\Model\Rule::RULE_TYPE_ONE,
                        'minimal_price' => 15
                    ],
                    [
                        'rule_id' => 3,
                        'sku' => 'ASQ1-4',
                        'qty' => 7,
                        'rule_type' => \Amasty\Promo\Model\Rule::RULE_TYPE_ALL,
                        'minimal_price' => 0
                    ]
                ],
                [
                    'common_qty' => 15
                ],
                0
            ],
            [
                [
                    [
                        'rule_id' => 1,
                        'sku' => 'ASQ1-1',
                        'qty' => 2,
                        'rule_type' => \Amasty\Promo\Model\Rule::RULE_TYPE_ONE,
                        'minimal_price' => 13,
                    ],
                    [
                        'rule_id' => 1,
                        'sku' => 'ASQ1-2',
                        'qty' => 3,
                        'rule_type' => \Amasty\Promo\Model\Rule::RULE_TYPE_ONE,
                        'minimal_price' => 10,
                    ],
                    [
                        'rule_id' => 2,
                        'sku' => 'ASQ1-3',
                        'qty' => 5,
                        'rule_type' => \Amasty\Promo\Model\Rule::RULE_TYPE_ONE,
                        'minimal_price' => 15
                    ],
                    [
                        'rule_id' => 3,
                        'sku' => 'ASQ1-4',
                        'qty' => 7,
                        'rule_type' => \Amasty\Promo\Model\Rule::RULE_TYPE_ALL,
                        'minimal_price' => 0
                    ]
                ],
                [
                    'common_qty' => 14
                ],
                1
            ]
        ];
    }

    /**
     * @param \Amasty\Promo\Model\ItemRegistry\PromoItemData[] $items
     *
     * @return \Amasty\Promo\Model\ItemRegistry\PromoItemRegistry|MockObject
     * @throws \ReflectionException
     */
    private function initPromoItemRegistry($items)
    {
        /** @var \Amasty\Promo\Model\ItemRegistry\PromoItemRegistry|MockObject $promoItemRegistry */
        $promoItemRegistry = $this->createPartialMock(\Amasty\Promo\Model\ItemRegistry\PromoItemRegistry::class, []);
        $this->setProperty(
            $promoItemRegistry,
            'storage',
            $items,
            \Amasty\Promo\Model\ItemRegistry\PromoItemRegistry::class
        );

        return $promoItemRegistry;
    }

    /**
     * @param array $dataArray
     *
     * @return \Amasty\Promo\Model\ItemRegistry\PromoItemData|MockObject
     */
    private function initItem($dataArray)
    {
        /** @var \Amasty\Promo\Model\ItemRegistry\PromoItemData|MockObject $item */
        $item = $this->createPartialMock(\Amasty\Promo\Model\ItemRegistry\PromoItemData::class, ['getQtyToProcess']);
        $item->expects($this->any())->method('getQtyToProcess')->willReturn($dataArray['qty']);

        $item->setSku($dataArray['sku'])
            ->setRuleId($dataArray['rule_id'])
            ->setRuleType($dataArray['rule_type'])
            ->setMinimalPrice($dataArray['minimal_price'])
            ->setDiscountItem('discountItem');

        return $item;
    }
}
