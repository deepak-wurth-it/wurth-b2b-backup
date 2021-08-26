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

use Mirasvit\Core\Api\Service\ValidatorInterface;
use Mirasvit\Core\Service\ValidationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State as AppState;

class ValidationCommand extends Command
{
    const COMPANY_NAMESPACE = 'Mirasvit';

    const INPUT_KEY_MODULES = 'module';
    const INPUT_KEY_INFO = 'info';

    /**
     * @var AppState
     */
    private $appState;

    /**
     * @var \Mirasvit\Core\Api\Service\ValidationServiceInterface
     */
    private $validationService;

    /**
     * ValidationCommand constructor.
     * @param AppState $appState
     * @param ValidationService $validationService
     */
    public function __construct(
        AppState $appState,
        ValidationService $validationService
    ) {
        $this->appState = $appState;
        $this->validationService = $validationService;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mirasvit:validate')
            ->setDescription('Validate Mirasvit Extensions')
            ->setDefinition($this->getInputList());

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->setAreaCode('global');
        } catch (\Exception $e) {
            # already set by another module
        }

        if (!$this->validationService->getValidators()) {
            $output->writeln('No validation available for installed modules');
            return;
        }

        if ($input->getOption(self::INPUT_KEY_INFO)) {
            $this->executeInfo($output);
        } else {
            $this->execValidation($input, $output);
        }
    }

    /**
     * Command definition.
     *
     * @return array
     */
    private function getInputList()
    {
        return [
            new InputOption(
                self::INPUT_KEY_INFO,
                '-i',
                InputOption::VALUE_NONE,
                'Shows allowed extension names'
            ),
            new InputArgument(
                self::INPUT_KEY_MODULES,
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Space-separated list of extension names or omit to validate all extensions.'
            ),
        ];
    }

    /**
     * Get label for status.
     *
     * @param int $status
     *
     * @return string
     */
    private function getStatusLabel($status)
    {
        $statusLabels = [
            ValidatorInterface::FAILED  => 'error',
            ValidatorInterface::WARNING => 'warning',
            ValidatorInterface::INFO    => 'info',
            ValidatorInterface::SUCCESS => 'success',
        ];

        return $statusLabels[$status];
    }

    /**
     * Get tag for status.
     *
     * @param int $status
     *
     * @return string
     */
    private function getStatusTag($status)
    {
        return $status > ValidatorInterface::INFO ? 'error' : 'info';
    }

    /**
     * Execute and render "info" command.
     *
     * @param OutputInterface $output
     *
     * @return void
     */
    private function executeInfo(OutputInterface $output)
    {
        foreach ($this->validationService->getValidators() as $validator) {
            $moduleCode = strtolower(explode('_', $validator->getModuleName())[1]);
            $output->writeln(sprintf('%-30s %s', $moduleCode, $validator->getModuleName()));
        }
    }

    /**
     * Execute "validation" command and render result.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    private function execValidation(InputInterface $input, OutputInterface $output)
    {
        $modules = array_map(function ($module) {
            return self::COMPANY_NAMESPACE . '_' . ucfirst($module);
        }, $input->getArgument(self::INPUT_KEY_MODULES));

        $result = $this->validationService->runValidation($modules);
        if (!$result) {
            $output->writeln(sprintf('Validation does exist for given extension(s): "%s"', implode(', ', $modules)));
            $output->writeln('Use one of the modules in a list below:');
            $this->executeInfo($output);
            return;
        }

        $table = new Table($output);
        $table->setHeaders(['Status', 'Module', 'Test', 'How to fix']);
        foreach ($result as $test) {
            $tag = $this->getStatusTag($test[ValidatorInterface::STATUS_CODE]);
            $status = "<$tag>" . strtoupper($this->getStatusLabel($test[ValidatorInterface::STATUS_CODE])) . "</$tag>";
            $table->addRow([
                $status,
                $test[ValidatorInterface::MODULE_NAME],
                $test[ValidatorInterface::TEST_NAME],
                $test[ValidatorInterface::MESSAGE],
            ]);
        }

        $table->render();
    }
}
