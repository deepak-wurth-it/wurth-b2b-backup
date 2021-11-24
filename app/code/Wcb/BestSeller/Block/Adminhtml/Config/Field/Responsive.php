<?php

namespace Wcb\BestSeller\Block\Adminhtml\Config\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class Responsive
 * @package Wcb\BestSeller\Block\Adminhtml\Config\Field
 */
class Responsive extends AbstractFieldArray
{
    /**
     * @inheritdoc
     */
    protected function _prepareToRender()
    {
        $this->addColumn('size', ['label' => __('Screen size max'), 'renderer' => false, 'class' => 'validate-digits']);
        $this->addColumn('items', ['label' => __('Number of items'), 'renderer' => false, 'class' => 'validate-digits']);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
