<?php

/**
 * Cron for Measure Of Units
 * @category  Wuerth
 * @package   Wcb_Cron
 * @author    Deepak Kumar <dkumar@Redstage.com>
 * @copyright 2022 - 2023 Wuerth IT
 */

namespace Wcb\Cron\PimCron;



class ImportUnitsOfMeasure
{



    public function __construct(
        \Pim\Product\Model\UnitsOfMeasureProcessor $UnitsOfMeasureProcessor
    ) {

        $this->UnitsOfMeasureProcessor = $UnitsOfMeasureProcessor;
    }

    public function execute()
    {
        try {
            $this->UnitsOfMeasureProcessor->install();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
