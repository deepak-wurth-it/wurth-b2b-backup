<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


namespace Amasty\Base\Observer;

use Amasty\Base\Model\Feed\ExtensionsProvider;
use Amasty\Base\Model\ModuleInfoProvider;
use Amasty\Base\Plugin\Backend\Model\Menu\Item;
use Magento\Config\Model\Config\Structure;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\Asset\Repository;

/**
 * @deprecated
 * @since 1.12.18
 * @see \Amasty\Base\Block\Adminhtml\System\Config\Information
 */
class GenerateInformationTab implements ObserverInterface
{
    const SEO_PARAMS = '?utm_source=extension&utm_medium=backend&utm_campaign=';

    const FEATURE_LINK = 'https://products.amasty.com/request-a-feature';

    const FEATURE_UTM = '?utm_source=extension&utm_medium=backend&utm_campaign=request_a_feature';

    const MAGENTO_VERSION = '_m2';

    private $block;

    /**
     * @var string
     */
    private $moduleLink;

    /**
     * @var string
     */
    private $moduleCode;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @var Structure
     */
    private $configStructure;

    /**
     * @var ExtensionsProvider
     */
    private $extensionsProvider;

    /**
     * @var ModuleInfoProvider
     */
    private $moduleInfoProvider;

    public function __construct(
        Manager $moduleManager,
        Repository $assetRepo,
        Structure $configStructure,
        ExtensionsProvider $extensionsProvider,
        ModuleInfoProvider $moduleInfoProvider
    ) {
        $this->moduleManager = $moduleManager;
        $this->assetRepo = $assetRepo;
        $this->configStructure = $configStructure;
        $this->extensionsProvider = $extensionsProvider;
        $this->moduleInfoProvider = $moduleInfoProvider;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $block = $observer->getBlock();
        if ($block) {
            $this->setBlock($block);
            $html = $this->generateHtml();
            $block->setContent($html);
        }
    }

    /**
     * @return string
     */
    private function generateHtml()
    {
        $html = '<div class="amasty-info-block">'
            . $this->showVersionInfo()
            . $this->additionalContent()
            . $this->showModuleExistingConflicts()
            . $this->getButtonsContainer();
        $html .= '</div>';

        return $html;
    }

    /**
     * @return string
     */
    protected function getLogoHtml()
    {
        $src = $this->assetRepo->getUrl('Amasty_Base::images/amasty_logo.svg');
        if ($this->moduleInfoProvider->isOriginMarketplace()) {
            $href = Item::MAGENTO_MARKET_URL;
        } else {
            $href = 'https://amasty.com' . $this->getSeoParams() . 'amasty_logo_' . $this->getModuleCode();
        }

        $html = '<a target="_blank" href="' . $href . '"><img class="amasty-logo" src="' . $src . '"/></a>';

        return $html;
    }

    /**
     * @return string
     */
    private function additionalContent()
    {
        $html = '';
        $content = $this->getBlock()->getAdditionalModuleContent();
        if ($content) {
            if (!is_array($content)) {
                $content = [
                    [
                        'type' => 'success',
                        'text' => $content
                    ]
                ];
            }

            foreach ($content as $message) {
                if (isset($message['type'], $message['text'])) {
                    $html .= '<div class="amasty-additional-content"><span class="message ' . $message['type'] . '">'
                        . $message['text']
                        . '</span></div>';
                }
            }
        }

        return $html;
    }

    /**
     * @return string
     */
    private function showVersionInfo()
    {
        $html = '<div class="amasty-module-version">';

        $currentVer = $this->getCurrentVersion();
        if ($currentVer) {
            $isVersionLast = $this->isLastVersion($currentVer);
            $class = $isVersionLast ? 'last-version' : '';
            $html .= '<div><span class="version-title">'
                . $this->getModuleName() . ' '
                . '<span class="module-version ' . $class . '">' . $currentVer . '</span>'
                . __(' by ')
                . '</span>'
                . $this->getLogoHtml()
                . '</div>';

            if (!$isVersionLast && !$this->moduleInfoProvider->isOriginMarketplace()) {
                $html .=
                    '<div><span class="upgrade-error message message-warning">'
                    . __(
                        'Update is available and recommended. See the '
                        . '<a target="_blank" href="%1">Change Log</a>',
                        $this->getChangeLogLink()
                    )
                    . '</span></div>';
            }
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * @return string|null
     */
    protected function getCurrentVersion()
    {
        $data = $this->moduleInfoProvider->getModuleInfo($this->getModuleCode());

        return isset($data['version']) ? $data['version'] : null;
    }

    /**
     * @return string
     */
    private function getModuleCode()
    {
        if (!$this->moduleCode) {
            $this->moduleCode = '';
            $class = get_class($this->getBlock());
            if ($class) {
                $class = explode('\\', $class);
                if (isset($class[0], $class[1])) {
                    $this->moduleCode = $class[0] . '_' . $class[1];
                }
            }
        }

        return $this->moduleCode;
    }

    /**
     * @return string
     */
    protected function getChangeLogLink()
    {
        return $this->getModuleLink()
            . $this->getSeoParams() . 'changelog_' . $this->getModuleCode() . '#changelog';
    }

    /**
     * @return string
     */
    private function getUserGuideContainer()
    {
        $html = '<div class="amasty-user-guide"><span class="message success">'
            . __(
                'Need help with the settings?'
                . '  Please  consult the <a target="_blank" href="%1">user guide</a>'
                . ' to configure the extension properly.',
                $this->getUserGuideLink()
            )
            . '</span></div>';

        return $html;
    }

    /**
     * @return string
     */
    private function getFeatureLink()
    {
        if ($this->moduleInfoProvider->isOriginMarketplace()) {
            return '';
        }

        return '<a href="' . self::FEATURE_LINK . self::FEATURE_UTM . '"
                   class="ambase-button"
                   target="_blank"
                   title="' . __("Request New Feature") . '">'
                . __("Request New Feature") . ' </a>';
    }

    /**
     * @return string
     */
    private function getButtonsContainer()
    {
        return '<div class="ambase-buttons-container">'
            . $this->getFeatureLink()
            . $this->getUserGuideContainer()
            . '</div>';
    }

    /**
     * @return string
     */
    private function getUserGuideLink()
    {
        $link = $this->getBlock()->getUserGuide();
        if ($link) {
            $seoLink = $this->getSeoParams();
            if (strpos($link, '?') !== false) {
                $seoLink = str_replace('?', '&', $seoLink);
            }

            $link .= $seoLink . 'userguide_' . $this->getModuleCode();
        }

        return $link;
    }

    /**
     * @return string
     */
    private function getSeoParams()
    {
        return self::SEO_PARAMS;
    }

    /**
     * @param string $currentVer
     *
     * @return bool
     */
    protected function isLastVersion($currentVer)
    {
        $result = true;

        $module = $this->extensionsProvider->getFeedModuleData($this->getModuleCode());
        if ($module
            && isset($module['version'])
            && version_compare($module['version'], (string)$currentVer, '>')
        ) {
            $result = false;
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function getModuleName()
    {
        $result = '';

        $configTabs = $this->configStructure->getTabs();
        if ($name = $this->findResourceName($configTabs)) {
            $result = $name;
        }

        if (!$result) {
            $module = $this->extensionsProvider->getFeedModuleData($this->getModuleCode());
            $result = __('Extension');
            if ($module && isset($module['name'])) {
                $result = $module['name'];
                $result = str_replace(' for Magento 2', '', $result);
            }
        }

        return $result;
    }

    /**
     * @param $config
     *
     * @return string
     */
    protected function findResourceName($config)
    {
        $result = '';
        $currentNode = null;
        foreach ($config as $node) {
            if ($node->getId() === 'amasty') {
                $currentNode = $node;
                break;
            }
        }

        if ($currentNode) {
            foreach ($currentNode->getChildren() as $item) {
                $data = $item->getData('resource');
                if (isset($data['label'], $data['resource'])
                    && strpos($data['resource'], $this->getModuleCode() . '::') !== false
                ) {
                    $result = $data['label'];
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    private function getModuleLink()
    {
        if (!$this->moduleLink) {
            $this->moduleLink = '';
            $module = $this->extensionsProvider->getFeedModuleData($this->getModuleCode());
            if ($module && isset($module['url'])) {
                $this->moduleLink = $module['url'];
            }
        }

        return $this->moduleLink;
    }

    /**
     * @return array
     */
    private function getExistingConflicts()
    {
        $conflicts = [];
        $module = $this->extensionsProvider->getFeedModuleData($this->getModuleCode());
        if ($module && isset($module['conflictExtensions'])) {
            $conflictsFromSite = $module['conflictExtensions'];
            $conflictsFromSite = str_replace(' ', '', $conflictsFromSite);
            $conflictsFromSite = explode(',', $conflictsFromSite);
            $conflicts = array_merge($conflicts, $conflictsFromSite);
            $conflicts = array_unique($conflicts);
        }

        return $conflicts;
    }

    /**
     * @return string
     */
    private function showModuleExistingConflicts()
    {
        $html = '';
        $messages = [];
        foreach ($this->getExistingConflicts() as $moduleName) {
            if ($this->moduleManager->isEnabled($moduleName)) {
                $messages[] = __(
                    'Incompatibility with the %1. '
                    . 'To avoid the conflicts we strongly recommend turning off '
                    . 'the 3rd party mod via the following command: "%2"',
                    $moduleName,
                    'magento module:disable ' . $moduleName
                );
            }
        }

        if (count($messages)) {
            $html = '<div class="amasty-conflicts-title">'
                . __('Problems detected:')
                . '</div>';

            $html .= '<div class="amasty-disable-extensions">';
            foreach ($messages as $message) {
                $html .= '<p class="message message-error">' . $message . '</p>';
            }

            $html .= '</div>';
        }

        return $html;
    }

    /**
     * @return mixed
     */
    public function getBlock()
    {
        return $this->block;
    }

    /**
     * @param mixed $block
     */
    public function setBlock($block)
    {
        $this->block = $block;
    }
}
