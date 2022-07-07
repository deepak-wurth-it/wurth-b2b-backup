<?php
namespace Wcb\NegotiableQuoteApi\Api;
/**
 * Interface RequestAQuoteManagementInterface
 * @api
 */
interface RequestAQuoteManagementInterface
{
    /**
     * Return getNegotiableQuoteList items.
     *
     * @param int $customerId
     * @param string $customerId
     * @return mixed
     */
    public function getNegotiableQuoteList($customerId,$customer_code);


}
