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

namespace Mirasvit\SearchAutocomplete\Plugin;

use Mirasvit\SearchAutocomplete\Model\ConfigProvider;
use Mirasvit\SearchAutocomplete\Model\IndexProvider;

/**
 * @see \Magento\Elasticsearch\Model\Adapter\BatchDataMapperInterface::map()
 * @see \Mirasvit\Search\Api\Data\Index\BatchDataMapperInterface::map()
 */
class AddInstantDataOnReindexPlugin
{
    private $indexIdentifier;

    private $storeId;

    private $configProvider;

    private $indexProvider;

    public function __construct(
        ConfigProvider $configProvider,
        IndexProvider $indexProvider
    ) {
        $this->configProvider = $configProvider;
        $this->indexProvider  = $indexProvider;
    }

    public function beforeMap(object $subject, array $documentData, int $storeId, array $context = []): array
    {
        $this->indexIdentifier = $context['entityType'] ?? 'catalogsearch_fulltext';
        $this->storeId         = $storeId;

        return [$documentData, $storeId, $context];
    }

    public function afterMap(object $subject, array $documentData): array
    {
        if (!$this->configProvider->isFastModeEnabled()) {
            return $documentData;
        }

        $index = $this->indexProvider->getIndex($this->indexIdentifier);
        if (!$index) {
            return $documentData;
        }

        $instantProvider = $this->indexProvider->getInstantProvider($index);
        if (!$instantProvider) {
            return $documentData;
        }


        $documentData = $instantProvider->map($documentData, $this->storeId);


        return $documentData;
    }
}
