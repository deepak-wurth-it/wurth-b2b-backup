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
 * @package   mirasvit/module-search-ultimate
 * @version   2.0.56
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Search\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Mirasvit\Search\Model\ConfigProvider;

class Wildcard implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => ConfigProvider::WILDCARD_INFIX,
                'label' => __('Enabled (*word*)'),
            ],
            [
                'value' => ConfigProvider::WILDCARD_SUFFIX,
                'label' => __('Enabled at end (word*)'),
            ],
            [
                'value' => ConfigProvider::WILDCARD_PREFIX,
                'label' => __('Enabled at start (*word)'),
            ],
            [
                'value' => ConfigProvider::WILDCARD_DISABLED,
                'label' => __('Disabled'),
            ],
        ];
    }
}
