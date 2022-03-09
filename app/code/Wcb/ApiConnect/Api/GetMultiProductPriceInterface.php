<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wcb\ApiConnect\Api;

interface GetMultiProductPriceInterface
{

    /**
     * POST for callMultiProductPrice api
     * @param mixed $skus
     * @return string[] 
     */
    public function callMultiProductPrice($skus);
}
