<?php

namespace Wcb\AttributelabelApi\Api;

interface ProductsInterface
{
     /**
     * Get info about product by product SKU
     *
     * @param string $sku
     * @param bool $editMode
     * @param int|null $storeId
     * @param bool $forceReload
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAdditional($sku, $editMode = false, $storeId = null, $forceReload = false);

}