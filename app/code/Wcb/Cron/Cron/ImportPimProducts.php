<?php

/**
 * Cron for products
 * @category  Wuerth
 * @package   Wcb_Cron
 * @author    Deepak Kumar <dkumar@Redstage.com>
 * @copyright 2022 - 2023 Wuerth IT
 */

namespace Wcb\Cron\Cron;



class ImportPimProducts
{



    public function __construct(
        \Pim\Product\Model\ProductProcessor $productProcessor
    ) {

        $this->productProcessor = $productProcessor;
    }

    public function execute()
    {
        try {
            $this->productProcessor->install();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
