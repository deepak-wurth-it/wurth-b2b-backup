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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Payment\Model\Config as PaymentConfig;

class PaymentMethod implements OptionSourceInterface
{
    /**
     * @var PaymentConfig
     */
    private $paymentConfig;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * PaymentMethod constructor.
     * @param PaymentConfig $paymentConfig
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        PaymentConfig $paymentConfig,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->paymentConfig = $paymentConfig;
        $this->scopeConfig = $scopeConfig;
    }
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];

        $paymentMethods = $this->paymentConfig->getActiveMethods();

        foreach (array_keys($paymentMethods) as $paymentCode) {
            $result[] = [
                'label' => $this->scopeConfig->getValue('payment/' . $paymentCode . '/title'),
                'value' => $paymentCode,
            ];
        }

        return $result;
    }
}
