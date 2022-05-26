<?php

/**
 * Cron for product attributes
 * @category  Wuerth
 * @package   Wcb_Cron
 * @author    Deepak Kumar <dkumar@Redstage.com>
 * @copyright 2022 - 2023 Wuerth IT
 */

namespace Wcb\Cron\PimCron;



class UpdateProductAttributeValues
{



    public function __construct(
        \Pim\Product\Model\ProductAttributeValueProcessor $productAttributeValueProcessor
    ) {

        $this->productAttributeValueProcessor = $productAttributeValueProcessor;
    }

    public function execute()
    {
        try {
            $this->productAttributeValueProcessor->install();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
