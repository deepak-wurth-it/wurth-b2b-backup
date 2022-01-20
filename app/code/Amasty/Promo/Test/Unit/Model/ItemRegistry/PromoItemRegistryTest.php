<?php

namespace Amasty\Promo\Test\Unit\Model\ItemRegistry;

use Amasty\Promo\Model\ItemRegistry\PromoItemRegistry;
use Amasty\Promo\Test\Unit\Traits;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class PromoItemRegistry
 *
 * @see PromoItemRegistry
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class PromoItemRegistryTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var PromoItemRegistry|MockObject
     */
    private $promoItemRegistry;

    /**
     * @var \Amasty\Promo\Model\ItemRegistry\PromoItemData|MockObject
     */
    private $promoItem;

    /**
     * @var \Amasty\Promo\Model\ItemRegistry\PromoItemFactory|MockObject
     */
    private $factory;

    public function setUp(): void
    {
        $this->promoItemRegistry = $this->createPartialMock(
            PromoItemRegistry::class, ['qtyAction']
        );

        $this->factory = $this->createMock(\Amasty\Promo\Model\ItemRegistry\PromoItemFactory::class);

        $this->setProperty(
            $this->promoItemRegistry,
            'factory',
            $this->factory,
            PromoItemRegistry::class
        );

        $this->setProperty(
            $this->promoItemRegistry,
            'sessionStorage',
            $this->createMock(\Magento\Checkout\Model\Session::class),
            PromoItemRegistry::class
        );
    }

    /**
     * @covers PromoItemRegistry::registerItem
     * @dataProvider registerItemDataProvider
     */
    public function testRegisterItem($sku, $qty, $ruleId, $expectedStorageCount)
    {
        $this->initPromoItem();
        $this->setProperty(
            $this->promoItemRegistry,
            'storage',
            [$this->promoItem],
            PromoItemRegistry::class
        );
        $this->factory->expects($this->any())->method('create')
            ->with($sku, $qty, $ruleId)
            ->willReturnCallback(
                function ($sku, $qty, $ruleId) {
                    $this->promoItem->setSku($sku)->setRuleId($ruleId);

                    return $this->promoItem;
                }
            );

        $result = $this->promoItemRegistry->registerItem($sku, $qty, $ruleId);
        $this->assertEquals($this->promoItem, $result);
        $storageCount = count(
            $this->getProperty($this->promoItemRegistry, 'storage', PromoItemRegistry::class)
        );
        $this->assertEquals($expectedStorageCount, $storageCount);
    }

    /**
     * @covers PromoItemRegistry::getItemBySkuAndRuleId
     * @dataProvider getItemBySkuAndRuleIdDataProvider
     */
    public function testGetItemBySkuAndRuleId($sku, $ruleId, $expected)
    {
        $this->initPromoItem();
        $this->setProperty(
            $this->promoItemRegistry,
            'storage',
            [$this->promoItem],
            PromoItemRegistry::class
        );

        $result = $this->promoItemRegistry->getItemBySkuAndRuleId($sku, $ruleId);
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers PromoItemRegistry::assignQtyToItem
     * @dataProvider assignQtyToItemDataProvider
     */
    public function testAssignQtyToItem($ruleType)
    {
        $qty = 1;
        $this->initPromoItem();
        $this->setProperty(
            $this->promoItemRegistry,
            'storage',
            [$this->promoItem],
            PromoItemRegistry::class
        );
        $this->promoItem->setRuleType($ruleType);

        $this->promoItemRegistry->expects($this->once())->method('qtyAction')
            ->with($qty, $this->promoItem, PromoItemRegistry::QTY_ACTION_RESERVE);

        $this->promoItemRegistry->assignQtyToItem($qty, $this->promoItem, PromoItemRegistry::QTY_ACTION_RESERVE);
    }

    /**
     * Data Provider for testGetItemBySkuAndRuleId test
     * @return array
     */
    public function getItemBySkuAndRuleIdDataProvider()
    {
        $this->initPromoItem();

        return [
            ['test_sku2', 1, null],
            ['test_sku', 2, null],
            ['test_sku', 1, $this->promoItem]
        ];
    }

    /**
     * Data Provider for assignQtyToItem test
     * @return array
     */
    public function assignQtyToItemDataProvider()
    {
        return [
            [1],
            [2]
        ];
    }

    /**
     * Data Provider for registerItem test
     * @return array
     */
    public function registerItemDataProvider()
    {
        return [
            ['test_sku', 1, 1, 1],
            ['test_sku2', 1, 2, 2]
        ];
    }

    /**
     * Init promo item mock for tests
     */
    private function initPromoItem()
    {
        $this->promoItem = $this->createPartialMock(
            \Amasty\Promo\Model\ItemRegistry\PromoItemData::class,
            []
        );
        $this->promoItem->setSku('test_sku')->setRuleId(1);
    }
}
