<?php
namespace Wcb\ProductSearchApi\Api;

/**
 * Interface ProductCompareManagementInterface
 * @api
 */
interface ProductSearchManagementInterface
{

    /**
     * Return ProductSearchApi items.
     *
     * @param int $customerId
     * @param string $search
     * @param int $group_id
     * @param int $page
     * @return array
     */
    public function getProductList($customerId,$search,$page,$group_id);

    /**
     * @param string $product_code
     * @return mixed
     */

    public function getProductByCode($product_code);

}
