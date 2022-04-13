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
 * @package   mirasvit/module-report
 * @version   1.3.112
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Report\Config\Source\Sales;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Shipping\Model\Config;
use Magento\Store\Model\ScopeInterface;

class ShippingMethod implements OptionSourceInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ShippingMethod constructor.
     * @param Config $config
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Config $config,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];

        $carriers = $this->config->getAllCarriers();
        foreach ($carriers as $carrierCode => $carrier) {
            $carrierMethods = $carrier->getAllowedMethods();
            if (!$carrierMethods) {
                continue;
            }
            $carrierTitle = $this->scopeConfig->getValue("carriers/$carrierCode/title", ScopeInterface::SCOPE_STORE);

            foreach ($carrierMethods as $methodCode => $methodTitle) {
                $result[] = [
                    'value' => $carrierCode . '_' . $methodCode,
                    'label' => $carrierTitle . ($methodTitle ? ' / ' . $methodTitle : ''),
                ];
            }
        }

        return $result;
    }
}
