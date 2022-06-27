<?php

/**
 * Cron order update from nav
 * @category  Wuerth
 * @package   Wcb_Cron
 * @author    Deepak Kumar <dkumar@Redstage.com>
 * @copyright 2022 - 2023 Wuerth IT
 */

namespace Wcb\Cron\WurthNavCron;



class UpdateOrderFromNav
{



    public function __construct(
        \WurthNav\Sales\Model\SalesOrderSyncFromNavProcessor $SalesOrderSyncFromNavProcessor
    ) {

        $this->SalesOrderSyncFromNavProcessor = $SalesOrderSyncFromNavProcessor;
    }

    public function execute()
    {
        try {
            $this->SalesOrderSyncFromNavProcessor->startProcess();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
