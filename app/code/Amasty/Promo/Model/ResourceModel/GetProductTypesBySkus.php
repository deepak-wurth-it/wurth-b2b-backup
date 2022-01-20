<?php

declare(strict_types=1);

namespace Amasty\Promo\Model\ResourceModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Temporary class, until the end of magento 2.2.* support.
 * class "\Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface" will be used instead afterwards
 * @sine 2.8.0
 */
class GetProductTypesBySkus
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * @param array $skus
     * @return array (key: 'sku', value: 'product_type')
     */
    public function execute(array $skus): array
    {
        $catalogConnection = $this->resource->getConnection('catalog');
        $productTable = $this->resource->getTableName('catalog_product_entity', 'catalog');

        $select = $catalogConnection->select()
            ->from($productTable, [ProductInterface::SKU, ProductInterface::TYPE_ID])
            ->where(ProductInterface::SKU . ' IN (?)', $skus);

        $result = [];
        foreach ($catalogConnection->fetchPairs($select) as $sku => $productType) {
            $result[$this->getResultKey((string)$sku, $skus)] = (string)$productType;
        }

        return $result;
    }

    /**
     * Return correct key for result array in GetProductTypesBySkus
     * Allows for different case sku to be passed in search array
     * with original cased sku to be passed back in result array
     *
     * @param string $sku
     * @param array $productSkuList
     * @return string
     */
    private function getResultKey(string $sku, array $productSkuList): string
    {
        $key = array_search(strtolower($sku), array_map('strtolower', $productSkuList));
        if ($key !== false) {
            $sku = (string)$productSkuList[$key];
        }

        return $sku;
    }
}
