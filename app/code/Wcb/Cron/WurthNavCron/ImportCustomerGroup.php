<?php

/**
 * Cron for Customer Group Import
 * @category  Wuerth
 * @package   Wcb_Cron
 * @author    Deepak Kumar <dkumar@Redstage.com>
 * @copyright 2022 - 2023 Wuerth IT
 */

namespace Wcb\Cron\WurthNavCron;



class ImportCustomerGroup
{



    public function __construct(
        \WurthNav\Customer\Model\CustomerGroupImportProcessor $customerGroupImportProcessor
    ) {

        $this->customerGroupImportProcessor = $customerGroupImportProcessor;
    }

    public function execute()
    {
        try {
            $this->customerGroupImportProcessor->install();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
