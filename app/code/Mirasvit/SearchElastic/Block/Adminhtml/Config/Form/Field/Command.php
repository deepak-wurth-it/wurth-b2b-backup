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



namespace Mirasvit\SearchElastic\Block\Adminhtml\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

abstract class Command extends Field
{
    /**
     * {@inheritdoc}
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('Mirasvit_Search::config/form/field/command.phtml');
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $buttonLabel  = $originalData['button_label'];

        $this->addData([
            'button_label' => __($buttonLabel),
            'html_id'      => $element->getHtmlId(),
            'ajax_url'     => $this->_urlBuilder->getUrl('search_elastic/command/' . $this->getAction()),
        ]);

        return $this->_toHtml();
    }

    /**
     * {@inheritdoc}
     */
    abstract protected function getAction();
}
