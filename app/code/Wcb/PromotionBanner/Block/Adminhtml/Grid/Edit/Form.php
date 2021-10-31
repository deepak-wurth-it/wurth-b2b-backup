<?php

namespace Wcb\PromotionBanner\Block\Adminhtml\Grid\Edit;

/**
 * Adminhtml Add New Row Form.
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context,
     * @param \Magento\Framework\Registry $registry,
     * @param \Magento\Framework\Data\FormFactory $formFactory,
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
     * @param \Wcb\PromotionBanner\Model\Status $options,
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Wcb\PromotionBanner\Model\Status $options,
        array $data = []
    ) {
        $this->_options = $options;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form.
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        
        $dateFormat = $this->_localeDate->getDateFormat(
            \IntlDateFormatter::MEDIUM
        );
        $timeFormat = $this->_localeDate->getTimeFormat(
            \IntlDateFormatter::MEDIUM
        );
        $model = $this->_coreRegistry->registry('row_data');
        $form = $this->_formFactory->create(
            ['data' => [
                            'id' => 'edit_form',
                            'enctype' => 'multipart/form-data',
                            'action' => $this->getData('action'),
                            'method' => 'post'
                        ]
            ]
        );

        $form->setHtmlIdPrefix('wkgrid_');
        if ($model->getEntityId()) {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Edit Row Data'), 'class' => 'fieldset-wide']
            );
            $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        } else {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Add Row Data'), 'class' => 'fieldset-wide']
            );
        }

        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title'),
                'id' => 'title',
                'title' => __('Title'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'image',
            'image', 
            [
                'name' => 'image',
                'label' => __('Upload Image'),
                'title' => __('Upload Image'),
                'required' => true,
                'note' => 'Allow image type: jpg, jpeg, png',
                'class' => 'required-entry required-file',
            ]
        );

        $fieldset->addField(
            'position',
            'select',
            [
                'name' => 'position',
                'label' => __('Banner Position'),
                'title' => __('Banner Position'),
                'class' => 'required-entry',
                'required' => true,
                "values"    => [
                    ["value" => '',"label" => __("-- Select --")],
                    ["value" => 'banner1',"label" => __("Banner 1 [1000 X 1022]")],
                    ["value" => 'banner2',"label" => __("Banner 2 [1000 X 482]")],
                    ["value" => 'banner3',"label" => __("Banner 3 [1000 X 482]")],
                    ["value" => 'banner4',"label" => __("Banner 4 [600 X 700]")],
                    ["value" => 'banner5',"label" => __("Banner 5 [600 X 700]")],
                    ["value" => 'banner6',"label" => __("Banner 6 [600 X 700]")],
                ]
            ]
        );

        $fieldset->addField(
            'url',
            'text',
            [
                'name' => 'url',
                'class' => 'validate-clean-url',
                "required" => true,
                'validation' => 'validate-clean-url',
                'label' => __('URL Link to Navigate'),
                'title' => __('URL Link to Navigate')
            ]
        );
					
        $fieldset->addField(
            'target',
            'select',
            [
                'name' => 'target',
                'label' => __('Banner Url Target'),
                'title' => __('Banner Url Target'),
				"values"    => [
                    ["value" => '_self',"label" => __("Same Page")],
                    ["value" => '_blank',"label" => __("New Tab")],
                ]
            ]
        );
        
        $fieldset->addField(
            'customer_group',
            'multiselect',
            [
                'label' => __('Customer Group to apply'),
                'title' => __('Customer Group to apply'),
                'name' => 'customer_group',
				"values"    => [
                    ["value" => 'Auto(A)',"label" => __("Auto(A)")],
                    ["value" => 'Cargo(C)',"label" => __("Cargo(C)")],
                    ["value" => 'Građevina(G)',"label" => __("Građevina(G)")],
                    ["value" => 'Metal(M)',"label" => __("Metal(M)")],
                    ["value" => 'Industry(I)',"label" => __("Industry(I)")],
                    ["value" => 'Trade(T)',"label" => __("Trade(T)")],
                    ["value" => 'Auto Trade (B)',"label" => __("Auto Trade (B)")],
                    ["value" => 'Others',"label" => __("Others")],
                ]
                ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'values' => $this->_options->getOptionArray(),
                'class' => 'status',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'valid_from',
            'date',
            [
                'name' => 'valid_from',
                'label' => __('Banner Valid From'),
                'title' => __('Banner Valid From'),
                    'date_format' => $dateFormat,
                    'time_format' => $timeFormat,		
            ]
        );

        $fieldset->addField(
            'valid_to',
            'date',
            [
                'name' => 'valid_to',
                'label' => __('Banner Valid To'),
                'title' => __('Banner Valid To'),
                    'date_format' => $dateFormat,
                    'time_format' => $timeFormat,
                ]
        );
						
        $fieldset->addField(
            'sort_order',
            'text',
            [
                'name' => 'sort_order',
                'label' => __('Banner Sort Order'),
                'title' => __('Banner Sort Order')
            ]
        );
						
        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
