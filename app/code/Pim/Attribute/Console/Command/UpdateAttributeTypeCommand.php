<?php

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Pim\Attribute\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class UpdateAttributeTypeCommand extends Command
{


    public function __construct(
        \Pim\Attribute\Model\AttributeTypeProcessor $attributeProcessor
    ) {
        parent::__construct();
        $this->attributeProcessor = $attributeProcessor;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('pim:update:attribute:type')
            ->setDescription('Update Attribute Type');

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
            $this->attributeProcessor->initExecution();
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
