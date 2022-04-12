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



namespace Mirasvit\Misspell\Plugin;

use Mirasvit\Misspell\Model\ConfigProvider;

/**
 * @see \Magento\Indexer\Model\Config\Data::get()
 */

class IndexerConfigDataPlugin
{
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    public function aroundGet(object $subject, callable $proceed, ?string $path = null, ?string $default = null): array
    {
        $data = $proceed($path, $default);

        if (empty($data)) {
            return [];
        }

        if (!$this->configProvider->isMisspellEnabled() && !$this->configProvider->isFallbackEnabled()) {
            if (!$path && isset($data[ConfigProvider::INDEXER_ID])) {
                unset($data[ConfigProvider::INDEXER_ID]);
            } elseif ($path) {
                list($firstKey,) = explode('/', $path);
                if ($firstKey == ConfigProvider::INDEXER_ID) {
                    $data = $default;
                }
            }
        }

        return $data;
    }
}
