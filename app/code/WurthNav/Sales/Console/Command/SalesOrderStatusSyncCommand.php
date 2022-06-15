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
use \WurthNav\Sales\Model\SalesOrderStatusProcessor;

class SalesOrderStatusSyncCommand extends Command
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
        SalesOrderStatusProcessor $SalesOrderStatusProcessor,
        $name = null
    ) {
        parent::__construct($name);
        $this->appState = $appState;
        $this->salesOrderStatusProcessor = $SalesOrderStatusProcessor;

    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('nav:sales:order:status:sync')
            ->setDescription('Sales Order Status Sync');
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
			 $this->salesOrderStatusProcessor->startProcess();
            
        } catch (\Exception $e) {
             $output->writeln('<error>' . $e->getMessage() . '</error>');
             if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                 $output->writeln($e->getTraceAsString());
             }
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }
}
