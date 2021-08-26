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

namespace Mirasvit\LayeredNavigation\Block\Adminhtml\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\BlockInterface;

class Filters extends AbstractFieldArray
{
    /** @var Attribute|BlockInterface */
    private $attributeRenderer;

    /** @var Position|BlockInterface */
    private $positionRenderer;

    protected function _prepareToRender(): void
    {
        $this->_addAfter       = false;
        $this->_addButtonLabel = (string)__('Add');

        $this->addColumn('attribute_code', [
            'label'    => __('Attribute'),
            'renderer' => $this->getAttributeRenderer(),
        ]);

        $this->addColumn('position', [
            'label'    => __('Position'),
            'renderer' => $this->getPositionRenderer(),
        ]);

        parent::_construct();
    }

    protected function _prepareArrayRow(DataObject $row): void
    {
        $attrHash = $this->getAttributeRenderer()
            ->calcOptionHash($row->getData('attribute_code'));

        $posHash = $this->getPositionRenderer()
            ->calcOptionHash($row->getData('position'));

        $row->setData('option_extra_attrs', [
            'option_' . $attrHash => 'selected="selected"',
            'option_' . $posHash  => 'selected="selected"',
        ]);
    }

    private function getAttributeRenderer(): Attribute
    {
        if ($this->attributeRenderer == null) {
            $this->attributeRenderer = $this->getLayout()->createBlock(Attribute::class, '', [
                'data' => ['is_render_to_js_template' => true],
            ]);
        }

        return $this->attributeRenderer;
    }

    private function getPositionRenderer(): Position
    {
        if ($this->positionRenderer == null) {
            $this->positionRenderer = $this->getLayout()->createBlock(Position::class, '', [
                'data' => ['is_render_to_js_template' => true],
            ]);
        }

        return $this->positionRenderer;
    }
}
