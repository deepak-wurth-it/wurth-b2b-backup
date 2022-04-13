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



namespace Mirasvit\SearchSphinx\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem;

class Config
{
    protected $scopeConfig;

    protected $filesystem;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Filesystem $filesystem
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->filesystem  = $filesystem;
    }

    public function getHost(): string
    {
        return (string)$this->scopeConfig->getValue('search/engine/host');
    }

    public function getPort(): int
    {
        return (int)$this->scopeConfig->getValue('search/engine/port');
    }

    public function isSameServer(): bool
    {
        return (bool)$this->scopeConfig->getValue('search/engine/same_server');
    }

    public function getBinPath(): array
    {
        return array_map('trim', explode(',', $this->scopeConfig->getValue('search/engine/bin_path')));
    }

    public function isAutoRestartAllowed(): bool
    {
        return (bool)$this->scopeConfig->getValue('search/engine/auto_restart');
    }

    public function getCustomBasePath(): string
    {
        return (string)$this->scopeConfig->getValue('search/engine/extended/custom_base_path');
    }

    public function getAdditionalSearchdConfig(): string
    {
        return (string)$this->scopeConfig->getValue('search/engine/extended/custom_searchd');
    }

    public function getAdditionalIndexConfig(): string
    {
        return (string)$this->scopeConfig->getValue('search/engine/extended/custom_index');
    }

    public function getCustomCharsetTable(): string
    {
        return (string)$this->scopeConfig->getValue('search/engine/extended/custom_charset_table');
    }

    public function getSphinxConfigurationTemplate(): string
    {
        $path = dirname(dirname(__FILE__)) . '/etc/conf/sphinx.conf';

        return file_get_contents($path);
    }

    public function getSphinxIndexConfigurationTemplate(): string
    {
        $path = dirname(dirname(__FILE__)) . '/etc/conf/index.conf';

        return file_get_contents($path);
    }

    public function getDefaultCharsetTable(): string
    {
        $path = dirname(dirname(__FILE__)) . '/etc/conf/charset.conf';

        return file_get_contents($path);
    }

    public function isFastMode(): bool
    {
        return (bool)$this->scopeConfig->isSetFlag('searchautocomplete/general/fast_mode');
    }
}
