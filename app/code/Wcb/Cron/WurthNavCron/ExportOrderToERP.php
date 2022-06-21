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
        \WurthNav\Sales\Model\SalesOrderSyncToNavProcessorFactory $SalesOrderSyncToNavProcessorFactory
    ) {

        $this->SalesOrderSyncToNavProcessorFactory = $SalesOrderSyncToNavProcessorFactory;
    }

    public function execute()
    {
        try {
			$objectERPSales = $this->SalesOrderSyncToNavProcessorFactory->create(); 
            $objectERPSales->install();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
