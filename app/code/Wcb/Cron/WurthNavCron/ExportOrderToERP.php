<?php

/**
 * Cron for Customer Registration data export to ERP
 * @category  Wuerth
 * @package   Wcb_Cron
 * @author    Deepak Kumar <dkumar@Redstage.com>
 * @copyright 2022 - 2023 Wuerth IT
 */

namespace Wcb\Cron\WurthNavCron;



class ExportOrderToERP
{



    public function __construct(
        \WurthNav\Customer\Model\SalesOrderSyncToNavProcessor $salesOrderSyncToNavProcessor
    ) {

        $this->salesOrderSyncToNavProcessor = $salesOrderSyncToNavProcessor;
    }

    public function execute()
    {
        try {
            $this->salesOrderSyncToNavProcessor->install();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
