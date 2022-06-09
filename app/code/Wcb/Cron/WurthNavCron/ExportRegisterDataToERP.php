<?php

/**
 * Cron for Customer Registration data export to ERP
 * @category  Wuerth
 * @package   Wcb_Cron
 * @author    Deepak Kumar <dkumar@Redstage.com>
 * @copyright 2022 - 2023 Wuerth IT
 */

namespace Wcb\Cron\WurthNavCron;



class ExportRegisterDataToERP
{



    public function __construct(
        \WurthNav\Customer\Model\CustomerSyncProcessor $customerSyncProcessor
    ) {

        $this->customerSyncProcessor = $customerSyncProcessor;
    }

    public function execute()
    {
        try {
            $this->customerSyncProcessor->install();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
