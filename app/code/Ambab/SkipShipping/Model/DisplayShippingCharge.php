<?php
/**
 * Ambab SkipShipping Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ambab
 * @package     Ambab_SkipShipping
 * @copyright   Copyright (c) 2019 Ambab (https://www.ambab.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Ambab\SkipShipping\Model;

use \Magento\Checkout\Model\ConfigProviderInterface;
use \Ambab\SkipShipping\Helper\Data as SkipshippingHelper;

class DisplayShippingCharge implements ConfigProviderInterface
{
    public $skipshippingHelper;

    public function __construct(
        SkipshippingHelper $skipshippingHelper
    ) {
        $this->skipshippingHelper = $skipshippingHelper;
    }

    /**
     *
     *  All the admin configuration
        @return array
     *
     */
    public function getConfig()
    {
        $config = [];
        $config['ambabSkipShippingSettings'] = $this->getSettings();
        return $config;
    }

    /**
     *
     *  get configuration setting from admin
        @return array
     *
     */

    public function getSettings()
    {
        $settings = [];
        if ($this->skipshippingHelper->isEnabled()) {
            $showShipping=$this->skipshippingHelper->canShowShippingInTotals();
        } else {
            $showShipping=0;
        }
        $settings['hideShippingCharge'] = $showShipping;
        $settings['isEnabled'] = $this->skipshippingHelper->isEnabled();
        return $settings;
    }
}
