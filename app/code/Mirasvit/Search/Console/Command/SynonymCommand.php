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



namespace Mirasvit\Search\Console\Command;

use Mirasvit\Search\Repository\SynonymRepository;
use Mirasvit\Search\Service\SynonymService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SynonymCommand extends Command
{
    const INPUT_FILE   = 'file';
    const INPUT_STORE  = 'store';
    const INPUT_REMOVE = 'remove';

    private $repository;

    private $service;

    public function __construct(
        SynonymRepository $repository,
        SynonymService $service
    ) {
        $this->repository = $repository;
        $this->service    = $service;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $options = [
            new InputOption(self::INPUT_FILE, null, InputOption::VALUE_REQUIRED, 'Synonyms file'),
            new InputOption(self::INPUT_STORE, null, InputOption::VALUE_REQUIRED, 'Store Id'),
            new InputOption(self::INPUT_REMOVE, null, InputOption::VALUE_NONE, 'Remove all synonyms'),
        ];

        $this->setName('mirasvit:search:synonym')
            ->setDescription('Import synonyms')
            ->addUsage("--remove")
            ->addUsage("--remove --store=1")
            ->addUsage("--file=EN.yaml --store=1")
            ->setDefinition($options);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption(self::INPUT_REMOVE)) {

            $collection = $this->repository->getCollection();

            $storeId = (int)$input->getOption(self::INPUT_STORE);
            if ($storeId) {
                $collection->addFieldToFilter('store_id', $storeId);
            }

            $pb = new ProgressBar($output);

            $pb->start();
            foreach ($collection as $item) {
                $this->repository->delete($item);

                $pb->advance(1);
            }
            $pb->finish();
            $pb->clear();

            $output->writeln("<info>{$pb->getMaxSteps()} synonyms are removed.</info>");

            return 0;
        }

        if ($input->getOption(self::INPUT_FILE) && $input->getOption(self::INPUT_STORE)) {
            $file    = $input->getOption(self::INPUT_FILE);
            $storeId = $input->getOption(self::INPUT_STORE);

            $generator = $this->service->import($file, $storeId);

            $pb = new ProgressBar($output);

            $pb->start();
            foreach ($generator as $result) {
                $pb->advance(1);
            }
            $pb->finish();
            $pb->clear();

            $output->writeln("<info>{$pb->getMaxSteps()} synonyms were imported.</info>");

            return 0;
        }

        $help = new HelpCommand();
        $help->setCommand($this);

        return $help->run($input, $output);
    }
}
