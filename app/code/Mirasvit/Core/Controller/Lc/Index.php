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



namespace Mirasvit\Core\Controller\Lc;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Mirasvit\Core\Model\LicenseFactory;
use Mirasvit\Core\Service\PackageService;

class Index extends Action
{
    private $packageService;

    private $licenseFactory;

    public function __construct(
        PackageService $packageService,
        LicenseFactory $licenseFactory,
        Context $context
    ) {
        $this->packageService = $packageService;
        $this->licenseFactory = $licenseFactory;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD)
     */
    public function execute()
    {
        echo '<pre>';

        foreach ($this->packageService->getPackageList() as $package) {
            foreach ($package->getModuleList() as $moduleName) {
                $license = $this->licenseFactory->create();

                list(, $name) = explode('_', $moduleName);

                $license->clear();

                $data = [
                    $moduleName,
                    $package->getVersion(),
                    $license->load('\\' . $name),
                    $license->getStatus('\\' . $name),
                    $package->getVersionTxt(),
                ];

                foreach ($data as $v) {
                    echo $this->renderVal($v) . "\t";
                }

                echo PHP_EOL;
            }
        }

        exit;
    }

    /**
     * @param string|bool $value
     *
     * @return string
     */
    private function renderVal($value)
    {
        if (is_bool($value)) {
            $value = $value ? 'T' : 'F';
        } else {
            $value = $value ? $value : '_';
        }
        $l = 20 - strlen($value);
        if ($l < 0) {
            $l = 0;
        }

        return '[ ' . $value . ' ]' . str_repeat(' ', $l);
    }
}
