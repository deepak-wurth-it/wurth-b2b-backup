<?php
namespace Wcb\ProductCompareApi\Api;

/**
 * Interface ProductCompareManagementInterface
 * @api
 */
interface ProductCompareManagementInterface
{

    /**
     * Return ProductCompareApi items.
     *
     * @param int $customerId
     * @return array
     */
    public function getProductCompareForCustomer($customerId);

    /**
     * Return ProductCompareApi status.
     *
     * @param int $customerId
     * @return array
     */
    public function clearProductCompareForCustomer($customerId);


    /**
     * Return Added ProductCompare item.
     *
     * @param int $customerId
     * @param int $productId
     * @return array
     *
     */
    public function addProductCompareForCustomer($customerId, $productId);

    /**
     * Return Delete ProductCompare status.
     *
     * @param int $customerId
     * @param int $wishlistId
     * @return array
     *
     */
    public function deleteProductCompareForCustomer($customerId, $compareListItemId);
}
