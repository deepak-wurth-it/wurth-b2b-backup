<?php

/**
 * Cron for Customer Address Import
 * @category  Wuerth
 * @package   Wcb_Cron
 * @author    Deepak Kumar <dkumar@Redstage.com>
 * @copyright 2022 - 2023 Wuerth IT
 */

namespace Wcb\Cron\WurthNavCron;



class ImportCustomerAddress
{



    public function __construct(
        \WurthNav\Customer\Model\CustomerAddressImportProcessor $customerAddressImportProcessor
    ) {

        $this->customerAddressImportProcessor = $customerAddressImportProcessor;
    }

    public function execute()
    {
        try {
            $this->customerAddressImportProcessor->install();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
