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

namespace Mirasvit\SearchAutocomplete\InstantProvider;

if (php_sapi_name() == 'cli') {
    return;
}
$configFile = dirname(__DIR__, 4) . '/etc/instant.json';

if (stripos(__DIR__, 'vendor') !== false) {
    $configFile = dirname(__DIR__, 6) . '/app/etc/instant.json';
}

if (!file_exists($configFile)) {
    return;
}
$config = \Zend_Json::decode(file_get_contents($configFile));

if (!isset($config['-1/typeahead']) || $config['-1/typeahead'] == false) {
    return;
}

use Mirasvit\Search\Api\Data\QueryConfigProviderInterface;
use Mirasvit\Core\Service\CompatibilityService;

class TypeaheadProvider
{
    protected $configProvider;

    public function __construct(
        QueryConfigProviderInterface $configProvider
    ) {
        $this->configProvider   = $configProvider;
    }

    public function process(): ?string
    {
        $this->configProvider->setStoreId($this->getStoreId());
        $suggestions = $this->configProvider->getTypeaheadSuggestions($this->getQueryText());

        return json_encode($suggestions);
    }

    protected function getStoreId(): int
    {
        return filter_input(INPUT_GET, 'store_id') != null
            ? (int)filter_input(INPUT_GET, 'store_id')
            : 0;
    }

    protected function getQueryText(): string
    {
        return filter_input(INPUT_GET, 'q') != null
            ? filter_input(INPUT_GET, 'q')
            : '';
    }
}

$configProvider = new ConfigProvider($config);
$provider = new TypeaheadProvider($configProvider);
$html     = $provider->process();
/** mp comment start */
if (! CompatibilityService::isMarketplace()) {
    if ($html) {
        // @codingStandardsIgnoreStart
        echo $html;
        exit;
        // @codingStandardsIgnoreEnd
    }
}
/** mp comment end */
