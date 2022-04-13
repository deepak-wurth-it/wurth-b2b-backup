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



namespace Mirasvit\SearchMysql\SearchAdapter\Index;

use Magento\Framework\App\ResourceConnection;

class IndexNameResolver
{
    private $resource;

    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    public function getIndexName(string $identifier, array $dimensions): string
    {
        $dimension = current($dimensions);

        $storeId = is_object($dimension->getValue()) ? (int)$dimension->getValue()->getId() : (int)$dimension->getValue();

        return $this->getIndexNameByStoreId($identifier, $storeId);
    }

    public function getIndexNameByStoreId(string $identifier, int $storeId): string
    {
        $indexName = "mst_search_{$identifier}_scope{$storeId}";

        return $this->resource->getTableName($indexName);
    }
}
