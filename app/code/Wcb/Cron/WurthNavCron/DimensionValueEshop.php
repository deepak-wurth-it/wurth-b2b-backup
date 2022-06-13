<?php

/**
 * Cron for Dimension Value Eshop
 * @category  Wuerth
 * @package   Wcb_Cron
 * @author    Deepak Kumar <dkumar@Redstage.com>
 * @copyright 2022 - 2023 Wuerth IT
 */

namespace Wcb\Cron\WurthNavCron;



class DimensionValueEshop
{



    public function __construct(
        \WurthNav\Customer\Model\DimensionValueEshopProcessor $dimensionValueEshopProcessor
    ) {

        $this->dimensionValueEshopProcessor = $dimensionValueEshopProcessor;
    }

    public function execute()
    {
        try {
            $this->dimensionValueEshopProcessor->install();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
