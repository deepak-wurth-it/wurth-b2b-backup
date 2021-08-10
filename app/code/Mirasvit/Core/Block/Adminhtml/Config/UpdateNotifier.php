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



namespace Mirasvit\Core\Block\Adminhtml\Config;

use Magento\Backend\Block\Template;
use Mirasvit\Core\Service\CompatibilityService;
use Mirasvit\Core\Service\PackageService;

class UpdateNotifier extends Template
{
    protected $_template = 'Mirasvit_Core::config/update-notifier.phtml';

    private   $packageService;

    public function __construct(
        PackageService $packageService,
        Template\Context $context
    ) {
        $this->packageService = $packageService;

        parent::__construct($context);
    }

    public function getNumberOfOutDates()
    {
        $packages = $this->packageService->getPackageList();
        $count    = 0;

        foreach ($packages as $package) {
            if ($package->getUrl() && $package->isOld()) {
                $count++;
            }
        }

        return $count;
    }

    public function getNumberOfTotal()
    {
        $packages = $this->packageService->getPackageList();
        $count    = 0;

        foreach ($packages as $package) {
            if ($package->getUrl()) {
                $count++;
            }
        }

        return $count;
    }

    public function toHtml()
    {
        return CompatibilityService::isMarketplace() ? '' : parent::toHtml();
    }
}
