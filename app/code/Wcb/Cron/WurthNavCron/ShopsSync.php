<?php

/**
 * Cron for Shops Syncs
 * @category  Wuerth
 * @package   Wcb_Cron
 * @author    Deepak Kumar <dkumar@Redstage.com>
 * @copyright 2022 - 2023 Wuerth IT
 */

namespace Wcb\Cron\WurthNavCron;



class ShopsSync
{



    public function __construct(
        \WurthNav\Customer\Model\ShopsProcessor $shopsProcessor
    ) {

        $this->shopsProcessor = $shopsProcessor;
    }

    public function execute()
    {
        try {
            $this->shopsProcessor->install();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
