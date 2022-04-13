<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search-ultimate
 * @version   2.0.56
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Search\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Store\Model\ScopeInterface;
use Mirasvit\Search\Api\Data\QueryConfigProviderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Search\Service\SynonymService;
use Mirasvit\Search\Service\StopwordService;
use Mirasvit\Search\Service\StemmingService;

class ConfigProvider implements QueryConfigProviderInterface
{
    private $scopeConfig;

    private $filesystem;

    private $storeManager;

    private $synonymService;

    private $stopwordService;

    private $stemmingService;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        SynonymService $synonymService,
        StopwordService $stopwordService,
        StemmingService $stemmingService
    ) {
        $this->scopeConfig      = $scopeConfig;
        $this->filesystem       = $filesystem;
        $this->storeManager     = $storeManager;
        $this->synonymService   = $synonymService;
        $this->stopwordService  = $stopwordService;
        $this->stemmingService  = $stemmingService;
    }

    public function getStoreId(): int
    {
        return (int) $this->storeManager->getStore()->getId();
    }

    public function getEngine(): string
    {
        return $this->scopeConfig->getValue('catalog/search/engine', ScopeInterface::SCOPE_STORE);
    }

    public function getLongTailExpressions(): array
    {
        if ($this->scopeConfig->getValue('search/advanced/long_tail_expressions', ScopeInterface::SCOPE_STORE) !== null) {
            $data = \Zend_Json::decode(
                $this->scopeConfig->getValue('search/advanced/long_tail_expressions', ScopeInterface::SCOPE_STORE)
            );
        } else {
            $data = [];
        }

        if (is_array($data)) {
            return array_values($data);
        }

        return [];
    }

    public function getReplaceWords(): array
    {
        if ($this->scopeConfig->getValue('search/advanced/replace_words', ScopeInterface::SCOPE_STORE) !== null) {
            $data = \Zend_Json::decode(
                $this->scopeConfig->getValue('search/advanced/replace_words', ScopeInterface::SCOPE_STORE)
            );
        } else {
            $data = [];
        }

        if (is_array($data)) {
            $result = [];
            foreach ($data as $item) {
                $from = explode(',', $item['from']);

                foreach ($from as $f) {
                    $result[] = [
                        'from' => trim($f),
                        'to'   => trim($item['to']),
                    ];
                }
            }

            return $result;
        }

        return [];
    }

    public function getNotWords(): array
    {
        $result = [];
        if ($this->scopeConfig->getValue('search/advanced/not_words', ScopeInterface::SCOPE_STORE) !== null) {
            $data = \Zend_Json::decode(
                $this->scopeConfig->getValue('search/advanced/not_words', ScopeInterface::SCOPE_STORE)
            );
        } else {
            $data = [];
        }


        if (is_array($data)) {
            foreach ($data as $row) {
                $result[] = $row['exception'];
            }
        }

        return $result;
    }


    public function getWildcardMode(): string
    {
        return $this->scopeConfig->getValue('search/advanced/wildcard', ScopeInterface::SCOPE_STORE);
    }

    public function getMatchMode(): string
    {
        return $this->scopeConfig->getValue('search/advanced/match_mode', ScopeInterface::SCOPE_STORE);
    }

    public function getWildcardExceptions(): array
    {
        $result = [];
        if ($this->scopeConfig->getValue('search/advanced/wildcard_exceptions', ScopeInterface::SCOPE_STORE) !== null) {
            $data = \Zend_Json::decode(
                $this->scopeConfig->getValue('search/advanced/wildcard_exceptions', ScopeInterface::SCOPE_STORE)
            );
        } else {
            $data = [];
        }

        if (is_array($data) && !empty($data)) {
            foreach ($data as $row) {
                $result[] = $row['exception'];
            }
        }

        return $result;
    }

    public function is404ToSearch(): bool
    {
        return (bool)$this->scopeConfig->getValue('search/feature/noroute_to_search', ScopeInterface::SCOPE_STORE);
    }

    public function isRedirectOnSingleResult(): bool
    {
        return (bool)$this->scopeConfig->getValue('search/feature/redirect_on_single_result', ScopeInterface::SCOPE_STORE);
    }

    public function isHighlightingEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue('search/feature/terms_highlighting', ScopeInterface::SCOPE_STORE);
    }

    public function isAddGoogleSiteLinks(): bool
    {
        return (bool)$this->scopeConfig->getValue('search/feature/google_sitelinks', ScopeInterface::SCOPE_STORE);
    }

    public function isMultiStoreModeEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue('search/multi_store_mode/enabled', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return array
     */
    public function getEnabledMultiStores()
    {
        return explode(
            ',',
            $this->scopeConfig->getValue('search/multi_store_mode/stores', ScopeInterface::SCOPE_STORE)
        );
    }

    /**
     * Stopwords paths
     * @return string Full path to directory with stopwords
     */
    public function getStopwordDirectoryPath()
    {
        return $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR)
            ->getAbsolutePath('sphinx/stopwords');
    }

    /**
     * Synonyms path
     * @return string Full path to directory with synonyms
     */
    public function getSynonymDirectoryPath()
    {
        return $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR)
            ->getAbsolutePath('sphinx/synonyms');
    }

    /**
     * @return bool
     */
    public function isFastMode()
    {
        return $this->scopeConfig->isSetFlag('searchautocomplete/general/fast_mode');
    }

    public function getTabsThreshold(): int
    {
        return (int)$this->scopeConfig->getValue('search/feature/tabs_threshold');
    }

    public function getSynonyms(array $terms, int $storeId): array
    {
        return $this->synonymService->getSynonyms($terms, $storeId);
    }

    public function isStopword(string $term, int $storeId): bool
    {
        return $this->stopwordService->isStopword($term, $storeId);
    }

    public function applyStemming(string $term): string
    {
        return $this->stemmingService->singularize($term);
    }

    public function applyLongTail(string $term): string
    {
        $expressions = $this->getLongTailExpressions();

        foreach ($expressions as $expr) {
            $matches = null;
            preg_match_all($expr['match_expr'], $term, $matches);

            foreach ($matches[0] as $math) {
                $math = preg_replace($expr['replace_expr'], $expr['replace_char'], $math);
                if ($math) {
                    $term = $math;
                }
            }
        }

        return $term;
    }

    public function getIgnoredIps(): array
    {
        $ignoredIps = [];

        if ($this->scopeConfig->getValue('search/feature/ignored_ips')) {
            $ignoredIps = preg_split('/\s*\,+\s*/', $this->scopeConfig->getValue('search/feature/ignored_ips'));
        }

        $ignoredIps = array_unique($ignoredIps);

        return $ignoredIps;
    }
}
