<?php

/**
 * Cron for images
 * @category  Wuerth
 * @package   Wcb_Cron
 * @author    Deepak Kumar <dkumar@Redstage.com>
 * @copyright 2022 - 2023 Wuerth IT
 */

namespace Wcb\Cron\PimCron;



class UpdateProductBarCode
{



    public function __construct(
        \Pim\Product\Model\ProductBarCodeProcessor $productBarCodeProcessor
    ) {

        $this->productBarCodeProcessor = $productBarCodeProcessor;
    }

    public function execute()
    {
        try {
            $this->productBarCodeProcessor->install();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
