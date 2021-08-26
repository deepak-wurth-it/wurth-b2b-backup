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

namespace Mirasvit\LayeredNavigation\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Mirasvit\LayeredNavigation\Api\Data\AttributeConfigInterface;

class VisibleInCategorySource implements ArrayInterface
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => AttributeConfigInterface::CATEGORY_VISIBILITY_MODE_ALL,
                'label' => __('All'),
            ],
            [
                'value' => AttributeConfigInterface::CATEGORY_VISIBILITY_MODE_SHOW_IN_SELECTED,
                'label' => __('Display only in Selected Categories'),
            ],
            [
                'value' => AttributeConfigInterface::CATEGORY_VISIBILITY_MODE_HIDE_IN_SELECTED,
                'label' => __('Hide only in Selected Categories'),
            ],
        ];
    }
}
