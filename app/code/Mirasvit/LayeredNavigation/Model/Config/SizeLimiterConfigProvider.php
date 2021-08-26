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

class SizeLimiterConfigProvider
{
    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function getDisplayMode(): string
    {
        return (string)$this->scopeConfig->getValue(
            'mst_nav/size_limiter/display_mode',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getLinkLimit(): int
    {
        return (int)$this->scopeConfig->getValue(
            'mst_nav/size_limiter/link_limit',
            ScopeInterface::SCOPE_STORE
        );
    }


    public function getScrollHeight(): int
    {
        return (int)$this->scopeConfig->getValue(
            'mst_nav/size_limiter/scroll_height',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getTextLess(): string
    {
        return (string)$this->scopeConfig->getValue(
            'mst_nav/size_limiter/text_less',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getTextMore(): string
    {
        return (string)$this->scopeConfig->getValue(
            'mst_nav/size_limiter/text_more',
            ScopeInterface::SCOPE_STORE
        );
    }
}
