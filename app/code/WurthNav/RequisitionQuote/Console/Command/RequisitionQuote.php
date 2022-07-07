<?php

namespace WurthNav\RequisitionQuote\Console\Command;

use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WurthNav\RequisitionQuote\Model\RequisitionQuoteSyncProcessor;

class RequisitionQuote extends Command
{
    /**
     * @var RequisitionQuoteSyncProcessor
     */
    protected $requisitionQuoteSyncProcessor;
    /**
     * @var State
     */
    protected $state;

    /**
     * RequisitionQuote constructor.
     * @param RequisitionQuoteSyncProcessor $requisitionQuoteSyncProcessor
     * @param State $state
     */
    public function __construct(
        RequisitionQuoteSyncProcessor $requisitionQuoteSyncProcessor,
        State $state
    ) {
        $this->requisitionQuoteSyncProcessor = $requisitionQuoteSyncProcessor;
        $this->state = $state;
        parent::__construct();
    }

    /**
     * Configure
     */
    protected function configure()
    {
        $this->setName('nav:requisitionquote:sync')
            ->setDescription('Requisition Quote Sync');

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
            $this->state->setAreaCode(Area::AREA_FRONTEND);
            $this->requisitionQuoteSyncProcessor->install();
        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($e->getTraceAsString());
            }
            // we must have an exit code higher than zero to indicate something was wrong
            return Cli::RETURN_FAILURE;
        }
    }
}
