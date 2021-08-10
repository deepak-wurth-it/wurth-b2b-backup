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



namespace Mirasvit\Core\Block\Adminhtml\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class SuggesterField extends Field
{
    protected function _renderScopeLabel(AbstractElement $element)
    {
        return '';
    }

    protected function _renderValue(AbstractElement $element)
    {
        $comment = $element->getData('comment');

        $html = '<td class="value">';
        $html .= '<span style="color: #f00;">' . __('Not Installed') . '</span>';
        $html .= '<p class="note">' . $comment . '</p>';
        $html .= '</td>';

        return $html;
    }
}
