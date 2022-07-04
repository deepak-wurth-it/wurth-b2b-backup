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
     * @param int $page
     * @return array
     */
    public function getProductList($customerId,$search,$page);


}