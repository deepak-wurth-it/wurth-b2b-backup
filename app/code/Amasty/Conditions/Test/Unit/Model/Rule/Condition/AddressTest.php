<?php

namespace Amasty\Conditions\Test\Unit\Model\Rule\Condition;

use Amasty\Base\Test\Unit\Traits;
use Amasty\Conditions\Api\Data\AddressInterface;
use Amasty\Conditions\Model\Rule\Condition\Address;
use PHPUnit\Framework\TestCase;

/**
 * Class AddressTest
 *
 * @see Address
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class AddressTest extends TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Address
     */
    private $model;

    /**
     * @covers Address::getInputType
     * @dataProvider getInputTypeDataProvider
     * @param string $type
     * @param string $result
     */
    public function testGetInputType($type, $result)
    {
        $attributeMock = $this->createPartialMock(Address::class, ['getAttribute']);
        $attributeMock->expects($this->any())->method('getAttribute')->willReturn($type);

        $this->assertEquals($result, $attributeMock->getInputType());
    }

    /**
     * @return array
     */
    public function getInputTypeDataProvider()
    {
        return [
            [
                AddressInterface::SHIPPING_ADDRESS_LINE,
                'string'
            ],
            [
                AddressInterface::CITY,
                'string'
            ],
            [
                AddressInterface::CURRENCY,
                'multiselect'
            ],
            [
                AddressInterface::PAYMENT_METHOD,
                'select'
            ],
            [
                null,
                'select'
            ]
        ];
    }
}
