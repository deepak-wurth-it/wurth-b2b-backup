<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wcb\ApiConnect\Api;

interface SingalProductStockInterface
{

    /**
     * POST for callSingalProductStock api
     * @param mixed $sku
     * @return mixed
     */
    public function callSingalProductStock($sku);
}
