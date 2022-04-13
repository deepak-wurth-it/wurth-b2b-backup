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

namespace Mirasvit\Misspell\Plugin;

use Magento\Elasticsearch\Model\Adapter\Index\Builder;
use Mirasvit\Misspell\Model\ConfigProvider;

/**
 * @see \Magento\Elasticsearch\Model\Adapter\Index\Builder::build()
 */
class AddSuggestAnalyzersPlugin
{
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    public function afterBuild(Builder $subject, array $settings): array
    {
        if (!$this->configProvider->isMisspellEnabled()) {
            return $settings;
        }

        $settings['analysis']['analyzer']['trigram'] = [
            'type'      => 'custom',
            'tokenizer' => 'standard',
            'filter'    => [
                'lowercase',
                'shingle',
            ],
        ];

        $settings['analysis']['analyzer']['reverse'] = [
            'type'      => 'custom',
            'tokenizer' => 'standard',
            'filter'    => [
                'lowercase',
                'reverse',
            ],
        ];

        $settings['analysis']['filter']['shingle'] = [
            'type'             => 'shingle',
            'min_shingle_size' => 2,
            'max_shingle_size' => 3,
        ];

        return $settings;
    }
}
