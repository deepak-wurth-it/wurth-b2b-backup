<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/

declare(strict_types=1);

namespace Amasty\Base\Block\Adminhtml\System\Config\InformationBlocks;

use Amasty\Base\Block\Adminhtml\System\Config\Information;
use Amasty\Base\Model\Feed\ExtensionsProvider;
use Amasty\Base\Model\ModuleInfoProvider;
use Amasty\Base\Plugin\Backend\Model\Menu\Item;
use Magento\Config\Model\Config\Structure;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Element\Template;

class VersionInfo extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_Base::config/information/version_info.phtml';

    /**
     * @var ModuleInfoProvider
     */
    private $moduleInfoProvider;

    /**
     * @var ExtensionsProvider
     */
    private $extensionsProvider;

    /**
     * @var Structure
     */
    private $configStructure;

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @var array
     */
    private $currentModuleFeedData = [];

    public function __construct(
        Template\Context $context,
        ModuleInfoProvider $moduleInfoProvider,
        ExtensionsProvider $extensionsProvider,
        Structure $structure,
        Repository $assetRepo,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleInfoProvider = $moduleInfoProvider;
        $this->extensionsProvider = $extensionsProvider;
        $this->configStructure = $structure;
        $this->assetRepo = $assetRepo;
    }

    public function getCurrentVersion(): string
    {
        $moduleCode = $this->getElement()->getDataByPath('group/module_code');

        return $this->moduleInfoProvider->getModuleInfo($moduleCode)['version'] ?? '';
    }

    public function getCurrentModuleFeedData(): array
    {
        if (!$this->currentModuleFeedData) {
            $moduleCode = $this->getElement()->getDataByPath('group/module_code');
            $this->currentModuleFeedData = $this->extensionsProvider->getFeedModuleData($moduleCode);
        }

        return $this->currentModuleFeedData;
    }

    public function isLastVersion(): bool
    {
        $currentVer = $this->getCurrentVersion();
        $feedData = $this->getCurrentModuleFeedData();

        if ($feedData
            && isset($feedData['version'])
            && version_compare($feedData['version'], $currentVer, '>')
        ) {
            return false;
        }

        return true;
    }

    public function getConfigModuleName(): string
    {
        $configPath = $this->getElement()->getDataByPath('group/path');
        $name = $this->configStructure->getElementByConfigPath($configPath)->getData()['label'] ?? '';

        if (!$name) {
            if ($this->getCurrentModuleFeedData() && isset($this->getCurrentModuleFeedData()['name'])) {
                $name = $this->getCurrentModuleFeedData()['name'];
                $name = str_replace(' for Magento 2', '', $name);
            } else {
                $name = __('Extension');
            }
        }

        return $name;
    }

    public function getLogoHref(): string
    {
        $moduleCode = $this->getElement()->getDataByPath('group/module_code');

        if ($this->moduleInfoProvider->isOriginMarketplace()) {
            $href = Item::MAGENTO_MARKET_URL;
        } else {
            $href = 'https://amasty.com' . Information::SEO_PARAMS . 'amasty_logo_' . $moduleCode;
        }

        return $href;
    }

    public function getLogoUrl(): string
    {
        return $this->assetRepo->getUrl('Amasty_Base::images/amasty_logo.svg');
    }

    public function isOriginMarketplace(): bool
    {
        return $this->moduleInfoProvider->isOriginMarketplace();
    }

    public function getChangelogLink(): string
    {
        if ($this->getCurrentModuleFeedData() && isset($this->getCurrentModuleFeedData()['url'])) {
            return $this->getCurrentModuleFeedData()['url'] . Information::SEO_PARAMS
                . 'changelog_' . $this->getElement()->getDataByPath('group/module_code')
                . '#changelog';
        }

        return '';
    }

    public function getElement(): AbstractElement
    {
        return $this->getParentBlock()->getElement();
    }
}
