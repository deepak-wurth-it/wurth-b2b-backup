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

namespace Mirasvit\SearchElastic\Plugin;

use Magento\Elasticsearch\Model\Adapter\BatchDataMapperInterface;
use Magento\Framework\Registry;
use Mirasvit\Search\Api\Data\IndexInterface;

/**
 * @see \Magento\Elasticsearch\Model\Adapter\BatchDataMapper\DataMapperResolver::map()
 */
class EntityTypeToDataMapperPlugin
{
    private $registry;

    function __construct(
        Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * Map index data for using in search engine metadata
     */
    public function beforeMap(BatchDataMapperInterface $subject, array $documentData, int $storeId, array $context = []): array
    {
        $indexIdentifier = $this->registry->registry(IndexInterface::IDENTIFIER);

        if ($indexIdentifier) {
            $context['entityType'] = $indexIdentifier;
        }

        return [$documentData, $storeId, $context];
    }
}
