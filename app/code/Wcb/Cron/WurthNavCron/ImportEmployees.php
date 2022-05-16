<?php

/**
 * Cron for Employee  Import
 * @category  Wuerth
 * @package   Wcb_Cron
 * @author    Deepak Kumar <dkumar@Redstage.com>
 * @copyright 2022 - 2023 Wuerth IT
 */

namespace Wcb\Cron\WurthNavCron;



class ImportEmployees
{



    public function __construct(
        \WurthNav\Customer\Model\EmployeesProcessor $employeesProcessor
    ) {

        $this->employeesProcessor = $employeesProcessor;
    }

    public function execute()
    {
        try {
            $this->employeesProcessor->install();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
