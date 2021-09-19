<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Pim\Product\Magento\Eav\Model\Entity\Attribute\Source\Boolean;

class UpdateRequired extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [
        ['value' => '1', 'label' => __('Yes')],
        ['value' => '0', 'label' => __('No')]
        ];
        return $this->_options;
    }
}
