<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


namespace Amasty\Base\Test\Unit\Model\Feed\FeedTypes;

use Amasty\Base\Helper\Module;
use Amasty\Base\Model\Feed\FeedTypes\News;
use Amasty\Base\Model\ModuleInfoProvider;
use Amasty\Base\Test\Unit\Traits;
use Magento\Framework\DataObjectFactory;

class NewsTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var News
     */
    private $model;

    /**
     * @var Module
     */
    private $moduleInfoProvider;

    protected function setUp(): void
    {
        $moduleList = $this->createMock(\Magento\Framework\Module\ModuleListInterface::class);
        $this->moduleInfoProvider = $this->createMock(ModuleInfoProvider::class);

        $moduleList->expects($this->any())->method('getNames')->willReturn(['Magento_Catalog', 'Amasty_Seo']);

        $dataObjectFactory = $this->createPartialMock(DataObjectFactory::class, ['create']);
        $dataObjectFactory->expects($this->any())->method('create')->willReturn(
            new \Magento\Framework\DataObject()
        );

        $this->model = $this->getObjectManager()->getObject(
            News::class,
            [
                'moduleList' => $moduleList,
                'moduleInfoProvider' => $this->moduleInfoProvider,
                'dataObjectFactory' => $dataObjectFactory
            ]
        );
    }

    /**
     * @covers NewsProcessor::getInstalledAmastyExtensions
     */
    public function testGetInstalledAmastyExtensions()
    {
        $this->assertEquals([1 => 'Amasty_Seo'], $this->invokeMethod($this->model, 'getInstalledAmastyExtensions'));
    }

    /**
     * @covers NewsProcessor::validateByExtension
     * @dataProvider validateByExtensionDataProvider
     */
    public function testValidateByExtension($extensions, $result)
    {
        $this->assertEquals($result, $this->invokeMethod($this->model, 'validateByExtension', [$extensions, true]));
    }

    /**
     * Data provider for validateByExtension test
     * @return array
     */
    public function validateByExtensionDataProvider()
    {
        return [
            ['', true],
            ['Magento_Catalog,Amasty_Seo', true],
            ['test', false],
        ];
    }

    /**
     * @covers NewsProcessor::validateByNotInstalled
     * @dataProvider validateByNotInstalledDataProvider
     */
    public function testValidateByNotInstalled($extensions, $result)
    {
        $this->assertEquals($result, $this->invokeMethod($this->model, 'validateByNotInstalled', [$extensions, true]));
    }

    /**
     * Data provider for validateByNotInstalled test
     * @return array
     */
    public function validateByNotInstalledDataProvider()
    {
        return [
            ['', true],
            ['Magento_Catalog,Amasty_Seo', true],
            ['Amasty_Seo', false],
        ];
    }

    /**
     * @covers NewsProcessor::getDependModules
     */
    public function testGetDependModules()
    {
        $this->moduleInfoProvider->expects($this->any())->method('getModuleInfo')
            ->willReturn(['name' => 'amasty', 'require' => ['magento' => 'catalog', 'amasty' => 'shopby']]);
        $this->assertEquals(['Amasty_Seo'], $this->invokeMethod($this->model, 'getDependModules', [['Amasty_Seo']]));
    }
}
