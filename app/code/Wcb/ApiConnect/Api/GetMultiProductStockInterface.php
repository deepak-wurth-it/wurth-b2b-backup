<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wcb\ApiConnect\Api;

interface GetMultiProductStockInterface
{

    /**
     * POST for callMultiProductStock api
     * @param mixed $skus
     * @return mixed
     */
    public function callMultiProductStock($skus);
}
