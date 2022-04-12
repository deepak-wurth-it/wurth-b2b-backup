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

namespace Mirasvit\Misspell\Model;

use Mirasvit\Misspell\Api\AdapterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\ObjectManagerInterface;

class ConfigProvider implements AdapterInterface
{
    const INDEXER_ID = 'mst_misspell';

    private $scopeConfig;

    private $objectManager;

    private $adapters;

    /**
     * @var AdapterInterface
     */
    private $adapter = null;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ObjectManagerInterface $objectManager,
        array $adapters = []
    ) {
        $this->scopeConfig   = $scopeConfig;
        $this->objectManager = $objectManager;
        $this->adapters      = $adapters;
    }

    public function getAdapter(): AdapterInterface
    {
        if ($this->adapter === null) {
            $engine = $this->getEngine();

            if (isset($this->adapters[$engine])) {
                return $this->objectManager->create($this->adapters[$engine]);
            }

            $this->adapter = $this->objectManager->create($this->adapters['mysql2']);
        }

        return $this->adapter;
    }

    public function reindex(int $storeId): void
    {
        $this->getAdapter()->reindex($storeId);
    }

    public function suggest(string $query): ?string
    {
        return $this->getAdapter()->suggest($query);
    }

    public function isMisspellEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue('misspell/general/active', ScopeInterface::SCOPE_STORE);
    }

    public function isFallbackEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue('misspell/general/fallback', ScopeInterface::SCOPE_STORE);
    }

    private function getEngine(): string
    {
        return $this->scopeConfig->getValue('catalog/search/engine', ScopeInterface::SCOPE_STORE);
    }
}
