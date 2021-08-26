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



namespace Mirasvit\Core\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param null $store
     *
     * @return string
     */
    public function getCustomCss($store = null)
    {
        return $this->scopeConfig->getValue(
            'mst_core/css/custom',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @return bool
     */
    public function isIncludeFontAwesome()
    {
        return (bool)$this->scopeConfig->getValue('mst_core/css/include_font_awesome');
    }

    /**
     * @return bool
     */
    public function isMenuEnabled()
    {
        return (bool)$this->scopeConfig->getValue('mst_core/menu/is_enabled');
    }

    /**
     * @return bool
     */
    public function isLessCompilationEnabled()
    {
        return (bool)$this->scopeConfig->getValue('mst_core/css/is_less_compilation_enabled');
    }

    /**
     * @return bool
     */
    public function isMarketplace()
    {
        $flag = true;

        /** mp comment start */

        $flag = false;

        /** mp comment end */

        return $flag;
    }
}
