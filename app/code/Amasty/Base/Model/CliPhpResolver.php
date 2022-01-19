<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


namespace Amasty\Base\Model;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Shell;
use Symfony\Component\Process\PhpExecutableFinder;

class CliPhpResolver
{
    const PHP_EXECUTABLE_PATH = 'php_executable_path';

    const VERSION_CHECK_REGEXP = '/PHP [\d\.]+ \(cli\)/';

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var PhpExecutableFinder
     */
    private $executableFinder;

    /**
     * @var Shell
     */
    private $shell;

    /**
     * @var string
     */
    private $executablePath;

    public function __construct(
        DeploymentConfig $deploymentConfig,
        PhpExecutableFinder $executableFinder,
        Shell $shell
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->executableFinder = $executableFinder;
        $this->shell = $shell;
    }

    /**
     * Return Cli PHP executable path.
     * Assumed that this executable will be executed through `exec` function
     *
     * @return string
     */
    public function getExecutablePath(): string
    {
        if (!$this->executablePath) {
            $this->executablePath = $this->resolvePhpExecutable();
        }

        return $this->executablePath;
    }

    private function resolvePhpExecutable()
    {
        $pathCandidates = [
            $this->deploymentConfig->get(self::PHP_EXECUTABLE_PATH),
            $this->executableFinder->find()
        ];

        foreach ($pathCandidates as $path) {
            if ($path && $this->isExecutable($path)) {
                return $path;
            }
        }

        return 'php';
    }

    private function isExecutable($path): bool
    {
        $disabledFunctions = $this->getDisabledPhpFunctions();
        if (in_array('exec', $disabledFunctions)) {
            throw new \RuntimeException(
                (string)__(
                    'The PHP function exec is disabled.'
                    . ' Please contact your system administrator or your hosting provider.'
                )
            );
        }

        try {
            $versionResult = (string)$this->shell->execute($path . ' %s', ['--version']);
        } catch (\Exception $e) {
            return false;
        }

        return (bool)preg_match(self::VERSION_CHECK_REGEXP, $versionResult);
    }

    private function getDisabledPhpFunctions(): array
    {
        return explode(',', str_replace(' ', ',', ini_get('disable_functions')));
    }
}
