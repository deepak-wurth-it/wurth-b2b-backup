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

namespace Mirasvit\Scroll\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Mirasvit\Scroll\Model\Config\Source\Mode;

class ConfigProvider
{
    private $_scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Get selector of blocks to which apply the infinity scroll widget.
     * @return string
     */
    public function getProductListSelector()
    {
        return $this->_scopeConfig->getValue('mst_scroll/general/product_list_selector', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->_scopeConfig->getValue('mst_scroll/general/mode', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getLoadPrevText()
    {
        return $this->_scopeConfig->getValue('mst_scroll/general/prev_text', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getLoadNextText()
    {
        return $this->_scopeConfig->getValue('mst_scroll/general/next_text', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return in_array($this->getMode(), [Mode::MODE_BUTTON, MODE::MODE_INFINITE], true);
    }
}
