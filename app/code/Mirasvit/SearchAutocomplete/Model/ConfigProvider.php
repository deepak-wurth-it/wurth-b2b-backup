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

namespace Mirasvit\SearchAutocomplete\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory as QueryCollectionFactory;
use Magento\Store\Model\ScopeInterface;
use Mirasvit\Core\Service\SerializeService;

class ConfigProvider
{
    const LAYOUT_1_COLUMN  = '1column';
    const LAYOUT_2_COLUMNS = '2columns';
    const LAYOUT_IN_PAGE   = 'in-page';

    private $queryCollectionFactory;

    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        QueryCollectionFactory $queryCollectionFactory
    ) {
        $this->scopeConfig            = $scopeConfig;
        $this->queryCollectionFactory = $queryCollectionFactory;
    }

    public function isShowImage(): bool
    {
        return (bool)$this->scopeConfig->getValue('searchautocomplete/general/product/show_image');
    }

    public function isShowRating(): bool
    {
        return (bool)$this->scopeConfig->getValue('searchautocomplete/general/product/show_rating');
    }

    public function isShowShortDescription(): bool
    {
        return (bool)$this->scopeConfig->getValue('searchautocomplete/general/product/show_description');
    }

    public function isShowSku(): bool
    {
        return (bool)$this->scopeConfig->getValue('searchautocomplete/general/product/show_sku');
    }

    public function isShowCartButton(): bool
    {
        return (bool)$this->scopeConfig->getValue('searchautocomplete/general/product/show_cart');
    }

    public function isShowStockStatus(): bool
    {
        return (bool)$this->scopeConfig->getValue('searchautocomplete/general/product/show_stock_status');
    }

    public function getShortDescriptionLen(): int
    {
        return 100;
    }

    public function isShowPrice(): bool
    {
        return (bool)$this->scopeConfig->getValue('searchautocomplete/general/product/show_price');
    }

    public function getDelay(): int
    {
        return (int)$this->scopeConfig->getValue('searchautocomplete/general/delay');
    }

    public function isFastModeEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue('searchautocomplete/general/fast_mode');
    }

    public function getMinChars(): int
    {
        return (int)$this->scopeConfig->getValue('searchautocomplete/general/min_chars');
    }

    public function getCssStyles(): string
    {
        return (string)$this->scopeConfig->getValue('searchautocomplete/general/appearance/css');
    }

    public function getIndexOptionValue(string $code, string $option, ?string $default = null): ?string
    {
        $options = $this->getIndexOptions($code);

        if (isset($options[$option])) {
            return $options[$option];
        }

        return $default !== null ? $default : null;
    }

    public function getIndexOptions(string $code): array
    {
        if (isset($this->getIndexesConfig()[$code])) {
            return $this->getIndexesConfig()[$code];
        }

        return [];
    }

    public function getIndexesConfig(): array
    {
        $result = [];
        if ($this->scopeConfig->getValue('searchautocomplete/general/index') !== null) {
            $result = SerializeService::decode($this->scopeConfig->getValue('searchautocomplete/general/index'));
        }

        return $result;
    }

    public function isShowPopularSearches(): bool
    {
        return (bool)$this->scopeConfig->getValue('searchautocomplete/popular/enabled', ScopeInterface::SCOPE_STORE);
    }

    public function getPopularSearches(): array
    {
        $result = $this->getDefaultPopularSearches();

        if (!count($result)) {
            $ignored = $this->getIgnoredSearches();

            $collection = $this->queryCollectionFactory->create()
                ->setPopularQueryFilter()
                ->setPageSize(20);

            /** @var \Magento\Search\Model\Query $query */
            foreach ($collection as $query) {
                $text      = $query->getQueryText();
                $isIgnored = false;
                foreach ($ignored as $word) {
                    if (strpos(strtolower($text), $word) !== false) {
                        $isIgnored = true;
                        break;
                    }
                }

                if (!$isIgnored) {
                    $result[] = mb_strtolower($text);
                }
            }
        }

        $result = array_slice(array_unique($result), 0, $this->getPopularLimit());
        $result = array_map('ucfirst', $result);

        return $result;
    }

    public function getDefaultPopularSearches(): array
    {
        $result = (string)$this->scopeConfig->getValue('searchautocomplete/popular/default', ScopeInterface::SCOPE_STORE);
        $result = array_filter(array_map('trim', explode(',', $result)));

        return $result;
    }

    public function getIgnoredSearches(): array
    {
        $result = (string)$this->scopeConfig->getValue('searchautocomplete/popular/ignored', ScopeInterface::SCOPE_STORE);
        $result = array_filter(array_map('strtolower', array_map('trim', explode(',', $result))));

        return $result;
    }


    public function getPopularLimit(): int
    {
        return (int)$this->scopeConfig->getValue('searchautocomplete/popular/limit', ScopeInterface::SCOPE_STORE);
    }


    public function isTypeAheadEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue('searchautocomplete/general/type_ahead');
    }

    public function getAutocompleteLayout(): string
    {
        return (string)$this->scopeConfig->getValue('searchautocomplete/general/appearance/layout');
    }

    public function getProductUrlSuffix(int $storeId): string
    {
        return (string)$this->scopeConfig->getValue(
            'catalog/seo/product_url_suffix',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getSearchEngine(): string
    {
        return (string)$this->scopeConfig->getValue('catalog/search/engine');
    }
}
