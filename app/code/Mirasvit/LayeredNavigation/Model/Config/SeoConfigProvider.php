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
 * @package   mirasvit/module-navigation
 * @version   2.0.12
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\LayeredNavigation\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class SeoConfigProvider
{
    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function getRobots(): string
    {
        return (string)$this->scopeConfig->getValue('mst_nav/seo/robots', ScopeInterface::SCOPE_STORE);
    }

    public function getCanonical(): string
    {
        return (string)$this->scopeConfig->getValue('mst_nav/seo/canonical', ScopeInterface::SCOPE_STORE);
    }

    public function getRelAttribute(): string
    {
        return (string)$this->scopeConfig->getValue('mst_nav/seo/rel', ScopeInterface::SCOPE_STORE);
    }
}
