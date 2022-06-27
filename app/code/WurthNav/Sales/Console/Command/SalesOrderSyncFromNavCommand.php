<?php

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


namespace WurthNav\Sales\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use WurthNav\Sales\Model\SalesOrderSyncFromNavProcessor;

class SalesOrderSyncFromNavCommand extends Command
{



    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;


    /**
     * QueryLogEnableCommand constructor.
     * @param Writer $deployConfigWriter
     * @param null $name
     */
    public function __construct(
        \Magento\Framework\App\State $appState,
        SalesOrderSyncFromNavProcessor $salesOrderSyncFromNavProcessor,
        $name = null
    ) {
        parent::__construct($name);
        $this->appState = $appState;
        $this->salesOrderSyncFromNavProcessor = $salesOrderSyncFromNavProcessor;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('nav:sales:order:sync:from:nav')
            ->setDescription('Sales order sync from nav');
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $output->setDecorated(true);
            $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
            $this->salesOrderSyncFromNavProcessor->startProcess();
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($e->getTraceAsString());
            }
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }
}
