<?php
namespace Wcb\Wishlist\Api;

/**
 * Interface WishlistManagementInterface
 * @api
 */
interface WishlistManagementInterface
{

    /**
     * Return Wishlist items.
     *
     * @param int $customerId
     * @return array
     */
    public function getWishlistForCustomer($customerId);

    /**
     * Return Added wishlist item.
     *
     * @param int $customerId
     * @param int $productId
     * @return array
     *
     */
    public function addWishlistForCustomer($customerId, $productId);

    /**
     * Return Added wishlist item.
     *
     * @param int $customerId
     * @param int $wishlistId
     * @return array
     *
     */
    public function deleteWishlistForCustomer($customerId, $wishlistItemId);
}
