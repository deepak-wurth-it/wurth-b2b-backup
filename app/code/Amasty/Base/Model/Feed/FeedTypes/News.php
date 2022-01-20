<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/

declare(strict_types=1);

namespace Amasty\Base\Model\Feed\FeedTypes;

use Amasty\Base\Model\AdminNotification\Model\ResourceModel\Inbox\Collection\ExistsFactory;
use Amasty\Base\Model\Config;
use Amasty\Base\Model\Feed\FeedContentProvider;
use Amasty\Base\Model\ModuleInfoProvider;
use Amasty\Base\Model\Parser;
use Amasty\Base\Model\Source\NotificationType;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Notification\MessageInterface;

class News
{
    protected $amastyModules = [];

    /**
     * @var Config
     */
    private $config;

    /**
     * @var FeedContentProvider
     */
    private $feedContentProvider;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var ExistsFactory
     */
    private $inboxExistsFactory;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var ModuleInfoProvider
     */
    private $moduleInfoProvider;

    public function __construct(
        Config $config,
        FeedContentProvider $feedContentProvider,
        Parser $parser,
        ProductMetadataInterface $productMetadata,
        ModuleListInterface $moduleList,
        ExistsFactory $inboxExistsFactory,
        Escaper $escaper,
        DataObjectFactory $dataObjectFactory,
        ModuleInfoProvider $moduleInfoProvider
    ) {
        $this->config = $config;
        $this->feedContentProvider = $feedContentProvider;
        $this->parser = $parser;
        $this->productMetadata = $productMetadata;
        $this->moduleList = $moduleList;
        $this->inboxExistsFactory = $inboxExistsFactory;
        $this->escaper = $escaper;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->moduleInfoProvider = $moduleInfoProvider;
    }

    /**
     * @return array
     */
    public function execute(): array
    {
        $feedData = [];
        $allowedNotifications = $this->config->getEnabledNotificationTypes();

        if (empty($allowedNotifications) || in_array(NotificationType::UNSUBSCRIBE_ALL, $allowedNotifications)) {
            return $feedData;
        }
        $maxPriority = 0;

        $feedResponse = $this->feedContentProvider->getFeedResponse(
            $this->feedContentProvider->getFeedUrl(FeedContentProvider::URN_NEWS)
        );
        $feedXml = $this->parser->parseXml($feedResponse->getContent());

        if (isset($feedXml->channel->item)) {
            $installDate = $this->config->getFirstModuleRun();
            foreach ($feedXml->channel->item as $item) {
                if ((int)$item->version === 1 // for magento One
                    || ((string)$item->edition && (string)$item->edition !== $this->getCurrentEdition())
                    || !array_intersect($this->convertToArray($item->type ?? ''), $allowedNotifications)
                ) {
                    continue;
                }
                $priority = $item->priority ?? 1;

                if ($priority <= $maxPriority || !$this->isItemValid($item)) {
                    continue;
                }
                $date = strtotime((string)$item->pubDate);
                $expired = isset($item->expirationDate) ? strtotime((string)$item->expirationDate) : null;

                if ($installDate <= $date && (!$expired || $expired > gmdate('U'))) {
                    $maxPriority = $priority;
                    $expired = $expired ? date('Y-m-d H:i:s', $expired) : null;

                    $feedData = [
                        'severity'        => MessageInterface::SEVERITY_NOTICE,
                        'date_added'      => date('Y-m-d H:i:s', $date),
                        'expiration_date' => $expired,
                        'title'           => $this->convertString($item->title),
                        'description'     => $this->convertString($item->description),
                        'url'             => $this->convertString($item->link),
                        'is_amasty'       => 1,
                        'image_url'       => $this->convertString($item->image)
                    ];
                }
            }
        }

        return $feedData;
    }

    /**
     * @param \SimpleXMLElement $item
     *
     * @return bool
     */
    protected function isItemValid(\SimpleXMLElement $item): bool
    {
        return $this->validateByExtension((string)$item->extension)
            && $this->validateByAmastyCount($item->amasty_module_qty)
            && $this->validateByNotInstalled((string)$item->amasty_module_not)
            && $this->validateByExtension((string)$item->third_party_modules, true)
            && $this->validateByDomainZone((string)$item->domain_zone)
            && !$this->isItemExists($item);
    }

    /**
     * @return string
     */
    protected function getCurrentEdition(): string
    {
        return $this->productMetadata->getEdition() === 'Community' ? 'ce' : 'ee';
    }

    /**
     * @param mixed $value
     *
     * @return array
     */
    private function convertToArray($value): array
    {
        return explode(',', (string)$value);
    }

    /**
     * @param \SimpleXMLElement $data
     *
     * @return string
     */
    private function convertString(\SimpleXMLElement $data): string
    {
        return $this->escaper->escapeHtml((string)$data);
    }

    /**
     * @return string[]
     */
    private function getAllExtensions(): array
    {
        $modules = $this->moduleList->getNames();
        $dispatchResult = $this->dataObjectFactory->create()->setData($modules);

        return $dispatchResult->toArray();
    }

    /**
     * @return string[]
     */
    private function getInstalledAmastyExtensions(): array
    {
        if (!$this->amastyModules) {
            $modules = $this->moduleList->getNames();

            $dispatchResult = new \Magento\Framework\DataObject($modules);
            $modules = $dispatchResult->toArray();

            $modules = array_filter(
                $modules,
                static function ($item) {
                    return strpos($item, 'Amasty_') !== false;
                }
            );
            $this->amastyModules = $modules;
        }

        return $this->amastyModules;
    }

    /**
     * @param string $extensions
     * @param bool $allModules
     *
     * @return bool
     */
    private function validateByExtension(string $extensions, bool $allModules = false): bool
    {
        if ($extensions) {
            $result = false;
            $arrExtensions = $this->getExtensionValue($extensions);

            if ($arrExtensions) {
                $installedModules = $allModules ? $this->getAllExtensions() : $this->getInstalledAmastyExtensions();
                $intersect = array_intersect($arrExtensions, $installedModules);
                if ($intersect) {
                    $result = true;
                }
            }
        } else {
            $result = true;
        }

        return $result;
    }

    /**
     * @param string $extensions
     *
     * @return bool
     */
    private function validateByNotInstalled(string $extensions): bool
    {
        if ($extensions) {
            $result = false;
            $arrExtensions = $this->getExtensionValue($extensions);

            if ($arrExtensions) {
                $installedModules = $this->getInstalledAmastyExtensions();
                $diff = array_diff($arrExtensions, $installedModules);
                if ($diff) {
                    $result = true;
                }
            }
        } else {
            $result = true;
        }

        return $result;
    }

    /**
     * @param string $extensions
     *
     * @return array
     */
    private function getExtensionValue(string $extensions): array
    {
        $arrExtensions = explode(',', $extensions);
        $arrExtensions = array_filter(
            $arrExtensions,
            static function ($item) {
                return strpos($item, '_1') === false;
            }
        );

        $arrExtensions = array_map(
            static function ($item) {
                return str_replace('_2', '', $item);
            },
            $arrExtensions
        );

        return $arrExtensions;
    }

    /**
     * @param int|string $counts
     *
     * @return bool
     */
    private function validateByAmastyCount($counts): bool
    {
        $result = true;

        $countString = (string)$counts;
        if ($countString) {
            $moreThan = null;
            $result = false;

            $position = strpos($countString, '>');
            if ($position !== false) {
                $moreThan = substr($countString, $position + 1);
                $moreThan = explode(',', $moreThan);
                $moreThan = array_shift($moreThan);
            }

            $arrCounts = $this->convertToArray($counts);
            $amastyModules = $this->getInstalledAmastyExtensions();
            $dependModules = $this->getDependModules($amastyModules);
            $amastyModules = array_diff($amastyModules, $dependModules);

            $amastyCount = count($amastyModules);

            if ($amastyCount
                && (
                    in_array($amastyCount, $arrCounts, false) // non strict
                    || ($moreThan && $amastyCount >= $moreThan)
                )
            ) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * @param string $zones
     *
     * @return bool
     */
    private function validateByDomainZone(string $zones): bool
    {
        $result = true;
        if ($zones) {
            $arrZones = $this->convertToArray($zones);
            $currentZone = $this->feedContentProvider->getDomainZone();

            if (!in_array($currentZone, $arrZones, true)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @param string[] $amastyModules
     *
     * @return array
     */
    private function getDependModules(array $amastyModules): array
    {
        $depend = [];
        $result = [];
        $dataName = [];
        foreach ($amastyModules as $module) {
            $data = $this->moduleInfoProvider->getModuleInfo($module);
            if (isset($data['name'])) {
                $dataName[$data['name']] = $module;
            }

            if (isset($data['require']) && is_array($data['require'])) {
                foreach ($data['require'] as $requireItem => $version) {
                    if (strpos($requireItem, 'amasty') !== false) {
                        $depend[] = $requireItem;
                    }
                }
            }
        }

        $depend = array_unique($depend);
        foreach ($depend as $item) {
            if (isset($dataName[$item])) {
                $result[] = $dataName[$item];
            }
        }

        return $result;
    }

    /**
     * @param \SimpleXMLElement $item
     *
     * @return bool
     */
    private function isItemExists(\SimpleXMLElement $item): bool
    {
        return $this->inboxExistsFactory->create()->execute($item);
    }
}
