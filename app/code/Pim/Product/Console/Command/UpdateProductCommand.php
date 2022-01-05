<?php

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Pim\Product\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class UpdateProductCommand extends Command
{


    public function __construct(
        \Pim\Product\Model\ProductUpdateProcessor $productUpdateProcessor,
        \Magento\Framework\App\State $state

    ) {
        
        $this->productUpdateProcessor = $productUpdateProcessor;
        $this->state = $state;
        parent::__construct();


    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('pim:update:product')
            ->setDescription('Update Product');

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
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
           
            $this->productUpdateProcessor->install();
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($e->getTraceAsString());
            }
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }
}
