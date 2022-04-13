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
use Magento\Sales\Model\Order\Config;

class OrderStatus implements OptionSourceInterface
{
    /**
     * @var Config
     */
    private $orderConfig;

    /**
     * OrderStatus constructor.
     * @param Config $orderConfig
     */
    public function __construct(
        Config $orderConfig
    ) {
        $this->orderConfig = $orderConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->orderConfig->getStatuses() as $value => $label) {
            $result[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        return $result;
    }
}
