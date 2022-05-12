<?php

namespace Wcb\Catalogslider\Block\Adminhtml\Catalogslider\Edit\Tab;

use IntlDateFormatter;
use Magento\Backend\Block\Template\Context;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Wcb\Catalogslider\Model\BlogPosts;
use Wcb\Catalogslider\Model\Status;

/**
 * Catalogslider edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Wcb\Catalogslider\Model\Status
     */
    protected $_status;
    /**
     * @var DataObject
     */
    private $_objectConverter;
    /**
     * @var GroupRepositoryInterface
     */
    private $_groupRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $_searchCriteriaBuilder;

    /**
     * Main constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Store $systemStore
     * @param Status $status
     * @param DataObject $objectConverter
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        Status $status,
        DataObject $objectConverter,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_objectConverter = $objectConverter;
        $this->_groupRepository = $groupRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Prepare label for tab
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Item Information');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Item Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    public function getTargetOptionArray()
    {
        return [
            '_self' => "Self",
            '_blank' => "New Page",
        ];
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model BlogPosts */
        $model = $this->_coreRegistry->registry('catalogslider');

        $isElementDisabled = false;

        /** @var Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                "required" => true,
                'label' => __('Banner Slider Title'),
                'title' => __('Banner Slider Title'),

                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'image',
            'image',
            [
                'name' => 'image',
                'label' => __('Image'),
                "required" => true,
                'title' => __('Image'),
                'note' => 'Allow image type: jpg, jpeg, gif, png',
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'url',
            'text',
            [
                'name' => 'url',
                "required" => true,
                'class' => 'validate-url',
                'validation' => 'validate-url',
                'label' => __('URL Link to Navigate'),
                'title' => __('URL Link to Navigate'),

                'disabled' => $isElementDisabled
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
                ],
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                "values" => [
                    ["value" => '1', "label" => __("Enabled")],
                    ["value" => '0', "label" => __("Disabled")],
                ]
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormat(
            IntlDateFormatter::MEDIUM
        );
        $timeFormat = $this->_localeDate->getTimeFormat(
            IntlDateFormatter::MEDIUM
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

                'disabled' => $isElementDisabled
            ]
        );

//        $dateFormat = $this->_localeDate->getDateFormat(
//            IntlDateFormatter::MEDIUM
//        );
//        $timeFormat = $this->_localeDate->getTimeFormat(
//            IntlDateFormatter::MEDIUM
//        );

        $fieldset->addField(
            'valid_to',
            'date',
            [
                'name' => 'valid_to',
                'label' => __('Banner Valid To'),
                'title' => __('Banner Valid To'),
                'date_format' => $dateFormat,
                'time_format' => $timeFormat,

                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'sort_order',
            'text',
            [
                'name' => 'sort_order',
                'label' => __('Banner Sort Order'),
                'title' => __('Banner Sort Order'),

                'disabled' => $isElementDisabled
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

        /* $fieldset->addField(
             'customer_group',
             'multiselect',
             [
                 'label' => __('Customer Group to apply'),
                 'title' => __('Customer Group to apply'),
                 'name' => 'customer_group',
                 "values" => [
                     ["value" => 'Auto(A)', "label" => __("Auto(A)")],
                     ["value" => 'Cargo(C)', "label" => __("Cargo(C)")],
                     ["value" => 'Građevina(G)', "label" => __("Građevina(G)")],
                     ["value" => 'Metal(M)', "label" => __("Metal(M)")],
                     ["value" => 'Industry(I)', "label" => __("Industry(I)")],
                     ["value" => 'Trade(T)', "label" => __("Trade(T)")],
                     ["value" => 'Auto Trade (B)', "label" => __("Auto Trade (B)")],
                     ["value" => 'Others', "label" => __("Others")],
                 ]
             ]
         );*/

        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
