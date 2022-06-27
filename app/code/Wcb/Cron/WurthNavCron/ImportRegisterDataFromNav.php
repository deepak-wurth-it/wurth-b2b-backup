<?php

/**
 * Cron for Customer Registration data import from ERP
 * @category  Wuerth
 * @package   Wcb_Cron
 * @author    Deepak Kumar <dkumar@Redstage.com>
 * @copyright 2022 - 2023 Wuerth IT
 */

namespace Wcb\Cron\WurthNavCron;



class ImportRegisterDataFromNav
{



    public function __construct(
        \WurthNav\Customer\Model\CustomerSyncProcessorFromNav $customerSyncProcessorFromNav
    ) {

        $this->customerSyncProcessorFromNav = $customerSyncProcessorFromNav;
    }

    public function execute()
    {
        try {
            $this->customerSyncProcessorFromNav->install();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
