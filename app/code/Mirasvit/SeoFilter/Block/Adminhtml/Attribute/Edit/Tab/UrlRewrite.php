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
 * @package   mirasvit/module-seo-filter
 * @version   1.1.5
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\SeoFilter\Block\Adminhtml\Attribute\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

class UrlRewrite extends Generic implements TabInterface
{

    private $formFactory;

    /** @var Attribute */
    private $attribute;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory
    ) {
        $this->formFactory = $formFactory;
        $this->attribute   = $registry->registry('entity_attribute');

        parent::__construct($context, $registry, $formFactory);
    }


    public function getTabLabel(): string
    {
        return (string)__('SEO Filters');
    }

    public function getTabTitle(): string
    {
        return $this->getTabLabel();
    }

    public function canShowTab(): bool
    {
        return true;
    }

    public function isHidden(): bool
    {
        return false;
    }

    protected function _prepareForm(): self
    {
        $form = $this->formFactory->create()->setData([
            'id'                => 'edit_form',
            'action'            => $this->getData('action'),
            'method'            => 'post',
            'enctype'           => 'multipart/form-data',
            'field_name_suffix' => 'seo_filter',
        ]);

        $frontendInput = $this->attribute->getFrontendInput();

        $form->addField('attribute_code', 'hidden', [
            'name'  => 'attribute_code',
            'value' => $this->attribute->getAttributeCode(),
        ]);

        $form->addField('attribute', Fieldset\AttributeFieldset::class, [
            Attribute::class => $this->attribute,
        ]);

        if (in_array($frontendInput, ['select', 'multiselect'])) {
            $options = $this->getLayout()->createBlock(Fieldset\OptionsFieldset::class);

            $this->setChild('form_after', $options);
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
