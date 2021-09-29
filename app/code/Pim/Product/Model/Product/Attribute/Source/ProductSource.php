<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Pim\Product\Model\Product\Attribute\Source;

class ProductSource extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [
        ['value' => 'pim', 'label' => __('PIM')],
        ['value' => 'magento_admin', 'label' => __('Created By Magento Admin')]
        ];
        return $this->_options;
    }
}
