<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/

declare(strict_types=1);

namespace Amasty\Base\Model\Feed\FeedTypes;

use Amasty\Base\Model\Feed\FeedContentProvider;
use Amasty\Base\Model\ModuleInfoProvider;
use Amasty\Base\Model\Parser;
use Amasty\Base\Model\Serializer;
use Magento\Framework\Config\CacheInterface;

class Ads
{
    const CSV_CACHE_ID = 'amasty_base_csv';
    const AMASTY_ADS_LAST_MODIFIED_DATE = 'amasty_ads_last_modified_date';

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var FeedContentProvider
     */
    private $feedContentProvider;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var Ad\Offline
     */
    private $adOffline;

    /**
     * @var ModuleInfoProvider
     */
    private $moduleInfoProvider;

    public function __construct(
        CacheInterface $cache,
        Serializer $serializer,
        FeedContentProvider $feedContentProvider,
        Parser $parser,
        Ad\Offline $adOffline,
        ModuleInfoProvider $moduleInfoProvider
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->feedContentProvider = $feedContentProvider;
        $this->parser = $parser;
        $this->adOffline = $adOffline;
        $this->moduleInfoProvider = $moduleInfoProvider;
    }

    /**
     * @return array
     */
    public function execute(): array
    {
        $cache = $this->cache->load(self::CSV_CACHE_ID);
        $unserializedCache = $cache ? $this->serializer->unserialize($cache) : null;

        return $unserializedCache ?: $this->getFeed();
    }

    /**
     * @return array
     */
    public function getFeed(): array
    {
        $result = [];
        $cachedData = $this->cache->load(self::CSV_CACHE_ID);
        $options = $cachedData ? ['modified_since' => $this->getLastModified()] : [];
        $feedResponse = $this->feedContentProvider->getFeedResponse(
            $this->feedContentProvider->getFeedUrl(FeedContentProvider::URN_ADS),
            $options
        );
        if (!$this->moduleInfoProvider->isOriginMarketplace()) {
            if ($feedResponse->isNeedToUpdateCache()) {
                $result = $this->parser->parseCsv($feedResponse->getContent());
                $result = $this->parser->trimCsvData($result, ['upsell_module_code', 'module_code']);
                $this->saveCache($result);
                $this->setLastModified();
            }
        }

        if (!$result || $feedResponse->isFailed()) {
            $result = $this->adOffline->getOfflineData($this->moduleInfoProvider->isOriginMarketplace());
            $result = $this->parser->trimCsvData($result, ['upsell_module_code', 'module_code']);
            $this->saveCache($result);
        }

        return $result;
    }

    private function getLastModified()
    {
        return $this->cache->load(self::AMASTY_ADS_LAST_MODIFIED_DATE);
    }

    private function setLastModified()
    {
        $dateTime = gmdate('D, d M Y H:i:s') . ' GMT';

        return $this->cache->save($dateTime, self::AMASTY_ADS_LAST_MODIFIED_DATE);
    }

    private function saveCache(array $result)
    {
        $this->cache->save(
            $this->serializer->serialize($result),
            self::CSV_CACHE_ID,
            [self::CSV_CACHE_ID]
        );
    }
}
