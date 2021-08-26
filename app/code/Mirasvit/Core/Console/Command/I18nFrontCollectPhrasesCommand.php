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
 * @package   mirasvit/module-core
 * @version   1.2.122
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Core\Console\Command;

use Magento\Framework\Filesystem\DirectoryList;
use Magento\Setup\Module\I18n\Dictionary\Options\ResolverFactory;
use Magento\Setup\Module\I18n\Dictionary\WriterInterface;
use Magento\Setup\Module\I18n\Factory as I18nFactory;
use Magento\Setup\Module\I18n\FilesCollector;
use Magento\Setup\Module\I18n\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class I18nFrontCollectPhrasesCommand extends Command
{
    const INPUT_KEY_MODULE_PATH = 'module_path';

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var I18nFactory
     */
    private $factory;

    /**
     * @var ResolverFactory
     */
    private $optionResolverFactory;

    /**
     * I18nFrontCollectPhrasesCommand constructor.
     * @param DirectoryList $directoryList
     * @param I18nFactory $factory
     * @param ResolverFactory $optionResolverFactory
     */
    public function __construct(
        DirectoryList $directoryList,
        I18nFactory $factory,
        ResolverFactory $optionResolverFactory
    ) {
        $this->directoryList = $directoryList;
        $this->factory = $factory;
        $this->optionResolverFactory = $optionResolverFactory;

        parent::__construct();

        $this->addAdapters();
    }

    /**
     * @var Parser\Parser
     */
    protected $parser;

    /**
     * @var WriterInterface
     */
    protected $writer;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mirasvit:collect:phrases')
            ->setDescription('Collect frontend phrases')
            ->setDefinition($this->getInputList());

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $withContext = false;
        $modulePath = $input->getArgument(self::INPUT_KEY_MODULE_PATH);
        $rootPath = $this->directoryList->getRoot();
        $directory = $rootPath . '/' . trim($modulePath, '/') . '/';

        if (!is_dir($directory)) {
            throw new \UnexpectedValueException('Wrong module path - ' . $directory);
        }

        if (!is_dir($directory . 'i18n/')) {
            mkdir($directory . 'i18n/');
        }

        $outputFilename = $directory . 'i18n/front_en_US.csv';
        $optionResolver = $this->optionResolverFactory->create($directory, $withContext);

        $options = $optionResolver->getOptions();
        foreach (array_keys($options) as $k) { // exclude backend files
            $options[$k]['fileMask'] = '/^(?:(?!\b[Aa]dminhtml\b).)*' . ltrim($options[$k]['fileMask'], '/');
        }

        $phraseList = $this->parser->parse($options);
        if (!count($phraseList)) {
            throw new \UnexpectedValueException('No phrases found in the specified dictionary file.');
        }
        foreach ($phraseList as $phrase) {
            $this->getDictionaryWriter($outputFilename)->write($phrase);
        }
        $this->writer = null;
    }

    /**
     * @return void
     */
    private function addAdapters()
    {
        $filesCollector = new FilesCollector();
        $phraseCollector = new Parser\Adapter\Php\Tokenizer\PhraseCollector(new Parser\Adapter\Php\Tokenizer());
        $adapters = [
            'php'  => new Parser\Adapter\Php($phraseCollector),
            'html' => new Parser\Adapter\Html(),
            'js'   => new Parser\Adapter\Js(),
            'xml'  => new Parser\Adapter\Xml(),
        ];

        $factory = new \Magento\Setup\Module\I18n\Factory();
        $parser = new Parser\Parser($filesCollector, $factory);
        foreach ($adapters as $type => $adapter) {
            $parser->addAdapter($type, $adapter);
        }

        $this->parser = $parser;
    }

    /**
     * @param string $outputFilename
     * @return WriterInterface
     */
    protected function getDictionaryWriter($outputFilename)
    {
        if (null === $this->writer) {
            $this->writer = $this->factory->createDictionaryWriter($outputFilename);
        }
        return $this->writer;
    }

    /**
     * Command definition.
     *
     * @return array
     */
    private function getInputList()
    {
        return [
            new InputArgument(
                self::INPUT_KEY_MODULE_PATH,
                InputArgument::REQUIRED,
                'Path to folder to parse. For example - "vendor/mirasvit/module-search-sphinx/src/SearchSphinx"'
            ),
        ];
    }
}
