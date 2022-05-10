<?php

namespace Wcb\PromotionBanner\Block\Adminhtml\Grid\Edit;

use IntlDateFormatter;
use Magento\Backend\Block\Template\Context;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Wcb\PromotionBanner\Model\Status;

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
     * Form constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Config $wysiwygConfig
     * @param Status $options
     * @param DataObject $objectConverter
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Config $wysiwygConfig,
        Status $options,
        DataObject $objectConverter,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        $this->_options = $options;
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_objectConverter = $objectConverter;
        $this->_groupRepository = $groupRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
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
            IntlDateFormatter::MEDIUM
        );
        $timeFormat = $this->_localeDate->getTimeFormat(
            IntlDateFormatter::MEDIUM
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
                "values" => [
                    ["value" => '', "label" => __("-- Select --")],
                    ["value" => 'banner1', "label" => __("Banner 1 [1000 X 638]")],
                    ["value" => 'banner2', "label" => __("Banner 2 [702 X 210]")],
                    ["value" => 'banner3', "label" => __("Banner 3 [702 X 210]")],
                    ["value" => 'banner4', "label" => __("Banner 4 [451 X 240]")],
                    ["value" => 'banner5', "label" => __("Banner 5 [451 X 240]")],
                    ["value" => 'banner6', "label" => __("Banner 6 [451 X 240]")],
                ]
            ]
        );

        $fieldset->addField(
            'url',
            'text',
            [
                'name' => 'url',
                'class' => 'validate-url',
                "required" => true,
                'validation' => 'validate-url',
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
                "values" => [
                    ["value" => '_self', "label" => __("Same Page")],
                    ["value" => '_blank', "label" => __("New Tab")],
                ]
            ]
        );

        $customerGroups = $this->_groupRepository->getList($this->_searchCriteriaBuilder->create())->getItems();
        $fieldset->addField('customer_group', 'multiselect', [
            'name' => 'customer_group[]',
            'label' => __('Customer Group to apply'),
            'title' => __('Customer Group to apply'),
            'required' => true,
            'values' => $this->_objectConverter->toOptionArray($customerGroups, 'id', 'code'),
            'note' => __('Select customer group(s) to display the block to')
        ]);
//        $fieldset->addField(
//            'customer_group',
//            'multiselect',
//            [
//                'label' => __('Customer Group to apply'),
//                'title' => __('Customer Group to apply'),
//                'name' => 'customer_group',
        //				"values"    => [
//                    ["value" => 'Auto(A)',"label" => __("Auto(A)")],
//                    ["value" => 'Cargo(C)',"label" => __("Cargo(C)")],
//                    ["value" => 'Građevina(G)',"label" => __("Građevina(G)")],
//                    ["value" => 'Metal(M)',"label" => __("Metal(M)")],
//                    ["value" => 'Industry(I)',"label" => __("Industry(I)")],
//                    ["value" => 'Trade(T)',"label" => __("Trade(T)")],
//                    ["value" => 'Auto Trade (B)',"label" => __("Auto Trade (B)")],
//                    ["value" => 'Others',"label" => __("Others")],
//                ]
//                ]
//        );

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
                //'time_format' => $timeFormat,
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
                //'time_format' => $timeFormat,
            ]
        );

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
