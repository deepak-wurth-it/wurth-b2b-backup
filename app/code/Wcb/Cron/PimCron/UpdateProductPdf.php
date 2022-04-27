<?php

/**
 * Cron for images
 * @category  Wuerth
 * @package   Wcb_Cron
 * @author    Deepak Kumar <dkumar@Redstage.com>
 * @copyright 2022 - 2023 Wuerth IT
 */

namespace Wcb\Cron\PimCron;



class UpdateProductPdf
{



    public function __construct(
        \Pim\Product\Model\ProductPdfProcessor $productPdfProcessor
    ) {

        $this->productPdfProcessor = $productPdfProcessor;
    }

    public function execute()
    {
        try {
            $this->productPdfProcessor->install();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
