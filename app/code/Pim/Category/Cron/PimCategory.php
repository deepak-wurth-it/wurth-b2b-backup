<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Pim\Category\Cron;

class PimCategory
{

    protected $logger;

    /**
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
       \Psr\Log\LoggerInterface $logger,
       \Pim\Category\Model\CategoryProcessor $categoryProcessor
    )
    {
        $this->logger = $logger;
        $this->categoryProcessor = $categoryProcessor;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        $this->categoryProcessor->initExecution();
        $this->logger->addInfo("Cronjob PimCategory is executed.");
    }
}

