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

class StateBarConfigProvider
{
    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function isHorizontalPosition(): bool
    {
        return $this->scopeConfig->getValue(
                'mst_nav/state_bar/position',
                ScopeInterface::SCOPE_STORE
            ) === 'horizontal';
    }


    public function isHidden(): bool
    {
        return $this->scopeConfig->getValue(
                'mst_nav/state_bar/position',
                ScopeInterface::SCOPE_STORE
            ) === 'hidden';
    }

    public function isFilterClearBlockInOneRow(): bool
    {
        return $this->scopeConfig->getValue(
                'mst_nav/state_bar/group_mode',
                ScopeInterface::SCOPE_STORE
            ) === 'group';
    }
}
