<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search-ultimate
 * @version   2.0.56
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchSphinx\Console\Command;

use Magento\Framework\App\State;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ManageCommand extends Command
{
    private $objectManager;

    private $state;

    public function __construct(
        ObjectManagerInterface $objectManager,
        State $state
    ) {
        $this->objectManager = $objectManager;
        $this->state         = $state;

        return parent::__construct();
    }

    protected function configure(): void
    {
        $options = [
            new InputOption('status'),
            new InputOption('stop'),
            new InputOption('start'),
            new InputOption('restart'),
            new InputOption('reset'),
            new InputOption('ensure'),
        ];

        $this->setName('mirasvit:search-sphinx:manage')
            ->setDescription('Sphinx engine management')
            ->setDefinition($options);

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        try {
            $this->state->setAreaCode('frontend');
        } catch (\Exception $e) {
        }

        $engine = $this->objectManager->get('Mirasvit\SearchSphinx\Model\Engine');

        if ($input->getOption('status')) {
            $out    = '';
            $result = $engine->status($out);
            if ($result) {
                $output->writeln("<comment>$out</comment>");
            } else {
                $output->writeln("<error>$out</error>");
            }
        }

        if ($input->getOption('start')) {
            $out    = '';
            $result = $engine->start($out);
            if ($result) {
                $output->writeln("<comment>$out</comment>");
            } else {
                $output->writeln("<error>$out</error>");
            }
        }

        if ($input->getOption('stop')) {
            $out    = '';
            $result = $engine->stop($out);
            if ($result) {
                $output->writeln("<comment>$out</comment>");
            } else {
                $output->writeln("<error>$out</error>");
            }
        }

        if ($input->getOption('restart')) {
            $out    = '';
            $result = $engine->restart($out);
            if ($result) {
                $output->writeln("<comment>$out</comment>");
            } else {
                $output->writeln("<error>$out</error>");
            }
        }

        if ($input->getOption('reset')) {
            $out    = '';
            $result = $engine->reset($out);
            if ($result) {
                $output->writeln("<comment>$out</comment>");
            } else {
                $output->writeln("<error>$out</error>");
            }
        }

        if ($input->getOption('ensure')) {
            if ($engine->status() == false) {
                $out    = '';
                $result = $engine->start($out);
                if ($result) {
                    $output->writeln("<comment>$out</comment>");
                } else {
                    $output->writeln("<error>$out</error>");
                }
            }
        }
    }
}
