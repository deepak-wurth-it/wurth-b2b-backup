<?php

/**
 * Cron for images
 * @category  Wuerth
 * @package   Wcb_Cron
 * @author    Deepak Kumar <dkumar@Redstage.com>
 * @copyright 2022 - 2023 Wuerth IT
 */

namespace Wcb\Cron\Cron;



class ImportImages
{



    public function __construct(
        \Pim\Product\Model\ProductImageProcessor $productImageProcessor
    ) {

        $this->productImageProcessor = $productImageProcessor;
    }

    public function execute()
    {
        try {
            $this->productImageProcessor->install();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}