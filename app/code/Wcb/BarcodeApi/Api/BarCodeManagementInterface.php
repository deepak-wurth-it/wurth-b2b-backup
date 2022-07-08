<?php
namespace Wcb\BarcodeApi\Api;

/**
 * Interface BarCodeManagementInterface
 * @api
 */
interface BarCodeManagementInterface
{

    /**
     * Return BarcodeApi items.
     *
     * @param int $bar_code
     * @return array
     */
    public function getProductByBarCode($bar_code);

}
