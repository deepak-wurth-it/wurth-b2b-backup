<?php

namespace Wcb\Cron\WurthNavCron;

use Exception;
use WurthNav\RequisitionQuote\Model\RequisitionQuoteSyncProcessor;

class RequisitionQuoteSync
{
    /**
     * @var RequisitionQuoteSyncProcessor
     */
    protected $requisitionQuoteSyncProcessor;

    /**
     * RequisitionQuoteSync constructor.
     * @param RequisitionQuoteSyncProcessor $requisitionQuoteSyncProcessor
     */
    public function __construct(
        RequisitionQuoteSyncProcessor $requisitionQuoteSyncProcessor
    ) {
        $this->requisitionQuoteSyncProcessor = $requisitionQuoteSyncProcessor;
    }

    /**
     * @return string
     */
    public function execute()
    {
        try {
            $this->requisitionQuoteSyncProcessor->install();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
