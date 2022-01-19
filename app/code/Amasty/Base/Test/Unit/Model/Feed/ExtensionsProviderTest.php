<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


namespace Amasty\Base\Test\Unit\Model\Feed;

use Amasty\Base\Model\Feed\ExtensionsProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ExtensionsProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getFeedModuleDataDataProvider
     */
    public function testGetFeedModuleData($modules, $expected)
    {
        $objectManager = new ObjectManager($this);
        $extensionsProvider = $objectManager->getObject(
            ExtensionsProvider::class,
            [
                'modulesData' => $modules
            ]
        );
        $this->assertEquals($expected, $extensionsProvider->getFeedModuleData('test1'));
    }

    public function getFeedModuleDataDataProvider()
    {
        return [
            [[], []],
            [['test1' => 'test1', 'test2' => 'test2'], 'test1']
        ];
    }
}
