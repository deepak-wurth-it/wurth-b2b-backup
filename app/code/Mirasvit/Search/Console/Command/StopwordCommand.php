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

use Mirasvit\Search\Repository\StopwordRepository;
use Mirasvit\Search\Service\StopwordService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StopwordCommand extends Command
{
    const INPUT_FILE   = 'file';
    const INPUT_STORE  = 'store';
    const INPUT_REMOVE = 'remove';

    private $repository;

    private $service;

    public function __construct(
        StopwordRepository $stopwordRepository,
        StopwordService $stopwordService
    ) {
        $this->repository = $stopwordRepository;
        $this->service    = $stopwordService;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $options = [
            new InputOption(self::INPUT_FILE, null, InputOption::VALUE_REQUIRED, 'Stopword file'),
            new InputOption(self::INPUT_STORE, null, InputOption::VALUE_REQUIRED, 'Store Id'),
            new InputOption(self::INPUT_REMOVE, null, InputOption::VALUE_NONE, 'Remove all stopwords'),
        ];

        $this->setName('mirasvit:search:stopword')
            ->setDescription('Import stopword')
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

            $store = (int)$input->getOption(self::INPUT_STORE);
            if ($store) {
                $collection->addFieldToFilter('store_id', $store);
            }

            $pb = new ProgressBar($output);

            $pb->start();
            foreach ($collection as $item) {
                $this->repository->delete($item);

                $pb->advance(1);
            }
            $pb->finish();
            $pb->clear();

            $output->writeln("<info>{$pb->getMaxSteps()} stopwords are removed.</info>");

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

            $output->writeln("<info>{$pb->getMaxSteps()} stopwords were imported.</info>");

            return 0;
        }

        $help = new HelpCommand();
        $help->setCommand($this);

        return $help->run($input, $output);
    }
}
