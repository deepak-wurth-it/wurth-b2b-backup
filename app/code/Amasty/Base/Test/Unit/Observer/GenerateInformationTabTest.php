<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


namespace Amasty\Base\Test\Unit\Observer;

use Amasty\Base\Model\Feed\ExtensionsProvider;
use Amasty\Base\Model\ModuleInfoProvider;
use Amasty\Base\Observer\GenerateInformationTab;
use Amasty\Base\Test\Unit\Traits;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class GenerateInformationTabTest
 *
 * @see GenerateInformationTab
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class GenerateInformationTabTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var GenerateInformationTab
     */
    private $observer;

    protected function setUp(): void
    {
        $block = $this->createPartialMock(
            \Magento\Config\Block\System\Config\Form\Fieldset::class,
            []
        );
        $block->setAdditionalModuleContent('test');

        $this->observer = $this->getObjectManager()->getObject(
            GenerateInformationTab::class,
            []
        );

        $this->setProperty($this->observer, 'block', $block, GenerateInformationTab::class);
    }

    /**
     * @covers Info::additionalContent
     */
    public function testAdditionalContent()
    {
        $this->assertEquals(
            '<div class="amasty-additional-content"><span class="message success">test</span></div>',
            $this->invokeMethod($this->observer, 'additionalContent')
        );
    }

    /**
     * @covers Info::showVersionInfo
     */
    public function testShowVersionInfo()
    {
        $this->observer = $this->createPartialMock(
            GenerateInformationTab::class,
            ['getCurrentVersion', 'isLastVersion', 'getModuleName', 'getLogoHtml', 'getChangeLogLink']
        );
        $moduleInfoProvider = $this->createMock(ModuleInfoProvider::class);
        $this->setProperty($this->observer, 'moduleInfoProvider', $moduleInfoProvider, GenerateInformationTab::class);
        $this->observer->expects($this->any())->method('getCurrentVersion')->willReturn('2.2.2');
        $this->observer->expects($this->any())->method('isLastVersion')->willReturn(false);
        $this->observer->expects($this->any())->method('getModuleName')->willReturn('test');
        $this->observer->expects($this->any())->method('getLogoHtml')->willReturn('test');
        $this->observer->expects($this->any())->method('getChangeLogLink')->willReturn('test');

        $result = $this->invokeMethod($this->observer, 'showVersionInfo');
        $this->assertTrue(
            (bool)strpos($result, 'upgrade-error')
        );
        $this->assertFalse(
            (bool)strpos($result, 'last-version')
        );
    }

    /**
     * @covers Info::getModuleCode
     */
    public function testGetModuleCode()
    {
        $block = $this->getObjectManager()->getObject(\Magento\Config\Block\System\Config\Form\Fieldset::class);
        $this->setProperty($this->observer, 'block', $block, GenerateInformationTab::class);
        $this->assertEquals('Magento_Config', $this->invokeMethod($this->observer, 'getModuleCode'));
    }

    /**
     * @covers Info::getUserGuideLink
     */
    public function testGetUserGuideLink()
    {
        $block = $this->getObjectManager()->getObject(\Magento\Config\Block\System\Config\Form\Fieldset::class);
        $block->setUserGuide('test');
        $this->setProperty($this->observer, 'block', $block, GenerateInformationTab::class);
        $this->assertEquals(
            'test?utm_source=extension&utm_medium=backend&utm_campaign=userguide_Magento_Config',
            $this->invokeMethod($this->observer, 'getUserGuideLink')
        );
    }

    /**
     * @covers Info::getModuleName
     */
    public function testGetModuleName()
    {
        $this->observer = $this->createPartialMock(
            GenerateInformationTab::class,
            ['findResourceName']
        );
        $configStructure = $this->createMock(\Magento\Config\Model\Config\Structure::class);
        $extensionsProvider = $this->createPartialMock(
            ExtensionsProvider::class,
            ['getFeedModuleData']
        );
        $extensionsProvider->expects($this->atLeastOnce())
            ->method('getFeedModuleData')
            ->willReturn('test');
        $this->setProperty($this->observer, 'extensionsProvider', $extensionsProvider, GenerateInformationTab::class);
        $this->observer->expects($this->any())->method('findResourceName')->willReturnOnConsecutiveCalls('test', '', '');

        $this->setProperty($this->observer, 'configStructure', $configStructure, GenerateInformationTab::class);
        $this->setProperty($this->observer, 'moduleCode', 'test_code', GenerateInformationTab::class);
        $this->assertEquals('test', $this->invokeMethod($this->observer, 'getModuleName'));
        $this->assertEquals('Extension', $this->invokeMethod($this->observer, 'getModuleName')->getText());

        $extensionsProvider = $this->createPartialMock(
            ExtensionsProvider::class,
            ['getFeedModuleData']
        );
        $extensionsProvider->expects($this->atLeastOnce())
            ->method('getFeedModuleData')
            ->willReturn(['name' => 'test for Magento 2']);
        $this->setProperty($this->observer, 'extensionsProvider', $extensionsProvider, GenerateInformationTab::class);

        $this->assertEquals('test', $this->invokeMethod($this->observer, 'getModuleName'));
    }
}
