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

namespace Mirasvit\Brand\Block\Adminhtml\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Mirasvit\Brand\Model\Config\GeneralConfig;

class UrlSuffix extends Field
{
    private $isCategoryUrlSuffixUsed = null;

    /**
     * @var GeneralConfig
     */
    private $generalConfig;

    public function __construct(
        GeneralConfig $generalConfig,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->generalConfig = $generalConfig;

        parent::__construct($context, $data);
    }

    public function isCategoryUrlSuffixUsed()
    {
        if ($this->isCategoryUrlSuffixUsed === null) {
            $this->isCategoryUrlSuffixUsed = $this->generalConfig->isCategoryUrlSuffix();
        }

        return $this->isCategoryUrlSuffixUsed;
    }

    /**
     * Retrieve HTML markup for given form element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $isCheckboxRequired = $this->_isInheritCheckboxRequired($element);

        // Disable element if value is inherited from other scope. Flag has to be set before the value is rendered.
        if ($this->isCategoryUrlSuffixUsed()) {
            $element->setDisabled(true);
        }

        $html = '<td class="label"><label for="' .
            $element->getHtmlId() . '"><span' .
            $this->_renderScopeLabel($element) . '>' .
            $element->getLabel() .
            '</span></label></td>';
        $html .= $this->_renderValue($element);

        if ($isCheckboxRequired) {
            $html .= $this->_renderInheritCheckbox($element);
        }

        $html .= $this->_renderHint($element);

        return $this->_decorateRowHtml($element, $html);
    }

    /**
     * Render inheritance checkbox (Use Default or Use Website)
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    protected function _renderInheritCheckbox(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $htmlId     = $element->getHtmlId();
        $namePrefix = preg_replace('#\[value\](\[\])?$#', '', $element->getName());
        $namePrefix = preg_replace('#]$#', '_category]', $namePrefix);
        $value      = $this->isCategoryUrlSuffixUsed()
            ? GeneralConfig::BRAND_URL_SUFFIX_CATEGORY_ON
            : GeneralConfig::BRAND_URL_SUFFIX_CATEGORY_OFF;

        $checkedHtml = $this->isCategoryUrlSuffixUsed() ? 'checked="checked"' : '';

        $html = '<td class="use-default">';
        $html .= '<input id="' .
            $htmlId .
            '_inherit" name="' .
            $namePrefix .
            '[value]" type="checkbox" value="' . $value . '"' .
            ' class="checkbox config-inherit" ' .
            $checkedHtml .
            ' onclick="toggleValueElements(this, Element.previous(this.parentNode));
            this.value = this.value == 1 ? 2 : 1; Element.next(this).value = this.value" /> ';
        $html .= '<input name="' .
            $namePrefix .
            '[value]" type="hidden" value="' . $value . '"' .
            ' class="checkbox config-inherit" ' .
            $checkedHtml . ' /> ';
        $html .= '<label for="' . $htmlId . '_inherit" class="inherit">' . $this->_getInheritCheckboxLabel(
            $element
        ) . '</label>';
        $html .= '</td>';

        return $html;
    }

    /**
     * @inheritdoc
     */
    protected function _getInheritCheckboxLabel(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return __('Use Category URL Suffix');
    }
}
