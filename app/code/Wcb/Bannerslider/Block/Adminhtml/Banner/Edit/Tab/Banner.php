<?php
/**
 * Copyright © 2015 PlazaThemes.com. All rights reserved.
 * @author PlazaThemes Team <contact@plazathemes.com>
 */

namespace Wcb\Bannerslider\Block\Adminhtml\Banner\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\ObjectFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Plazathemes\Bannerslider\Helper\Data;
use Plazathemes\Bannerslider\Model\Status;

class Banner extends Generic implements TabInterface
{
    /**
     * @var ObjectFactory
     */
    protected $_objectFactory;

    /**
     * @var Store
     */
    protected $_systemStore;

    /**
     * helper
     * @var Data
     */
    protected $_bannersliderHelper;

    /**
     * @var \Plazathemes\Bannerslider\Model\Banner
     */
    protected $_banner;
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
     * Banner constructor.
     * @param Context $context
     * @param Data $bannersliderHelper
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Store $systemStore
     * @param \Plazathemes\Bannerslider\Model\Banner $banner
     * @param DataObject $objectConverter
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $bannersliderHelper,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        \Plazathemes\Bannerslider\Model\Banner $banner,
        DataObject $objectConverter,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        $this->_localeDate = $context->getLocaleDate();
        $this->_systemStore = $systemStore;
        $this->_bannersliderHelper = $bannersliderHelper;
        $this->_banner = $banner;
        $this->_objectConverter = $objectConverter;
        $this->_groupRepository = $groupRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getBanner()
    {
        return $this->_coreRegistry->registry('banner');
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Banner Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Banner Information');
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

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('page.title')->setPageTitle($this->getPageTitle());

        Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock(
                'Plazathemes\Bannerslider\Block\Adminhtml\Form\Renderer\Fieldset\Element',
                $this->getNameInLayout() . '_fieldset_element'
            )
        );
    }

    public function getPageTitle()
    {
        // return $this->getBanner()->getId() ? __("Edit Banner '%1'", $this->escapeHtml($this->getBanner()->getName())) : __('New Banner');
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('banner');

        // $storeViewId = $this->getRequest()->getParam('store');

        /** @var Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix($this->_banner->getFormFieldHtmlIdPrefix());

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Banner Information')]);

        if ($model->getId()) {
            $fieldset->addField('banner_id', 'hidden', ['name' => 'banner_id']);
        }

        $elements = [];
        $elements['name'] = $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
            ]
        );

        /**
         * Check is single store mode
         */

        $elements['store_id'] = $fieldset->addField(
            'store_id',
            'multiselect',
            [
                'name' => 'stores[]',
                'label' => __('Store View'),
                'title' => __('Store View'),
                'required' => true,
                'values' => $this->_systemStore->getStoreValuesForForm(false, true),
            ]
        );

        $elements['title1'] = $fieldset->addField(
            'title1',
            'text',
            [
                'name' => 'title1',
                'label' => __('Title 1'),
                'title' => __('Title 1'),
            ]
        );

        $elements['title2'] = $fieldset->addField(
            'title2',
            'text',
            [
                'name' => 'title2',
                'label' => __('Title 2'),
                'title' => __('Title 2'),
            ]
        );

        $elements['status'] = $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Banner Status'),
                'name' => 'status',
                'options' => Status::getAvailableStatuses(),
            ]
        );

        $elements['image_alt'] = $fieldset->addField(
            'image_alt',
            'text',
            [
                'title' => __('Alt Text'),
                'label' => __('Alt Text'),
                'name' => 'image_alt',
                'note' => 'Used for SEO',
            ]
        );

        $elements['click_url'] = $fieldset->addField(
            'click_url',
            'text',
            [
                'title' => __('URL'),
                'label' => __('URL'),
                'name' => 'click_url',
            ]
        );

        $elements['image'] = $fieldset->addField(
            'image',
            'image',
            [
                'title' => __('Banner Image'),
                'label' => __('Banner Image'),
                'name' => 'image',
                'note' => 'Allow image type: jpg, jpeg, gif, png',
            ]
        );

        $elements['description'] = $fieldset->addField(
            'description',
            'textarea',
            [
                'title' => __('Description'),
                'label' => __('Description'),
                'name' => 'description',
            ]
        );

        $customerGroups = $this->_groupRepository->getList($this->_searchCriteriaBuilder->create())->getItems();
//        $fieldset->addField('customer_group', 'multiselect', [
//            'name' => 'customer_group[]',
//            'label' => __('Customer Group to apply'),
//            'title' => __('Customer Group to apply'),
//            'required' => true,
//            'values' => $this->_objectConverter->toOptionArray($customerGroups, 'id', 'code'),
//            'note' => __('Select customer group(s) to display the block to')
//        ]);

        $elements['visible_to'] = $fieldset->addField(
            'visible_to',
            'multiselect',
            [
                'title' => __('Select Customer Group to be Visible'),
                'label' => __('Select Customer Group to be Visible'),
                'name' => 'visible_to',
                "values" => $this->_objectConverter->toOptionArray($customerGroups, 'id', 'code')
            ]
        );

        /*$elements['visible_to'] = $fieldset->addField(
            'visible_to',
            'multiselect',
            [
                'title' => __('Select Customer Group to be Visible'),
                'label' => __('Select Customer Group to be Visible'),
                'name' => 'visible_to',
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
        $elements['valid_from'] = $fieldset->addField(
            'valid_from',
            'date',
            [
                'name' => 'valid_from',
                'title' => __('Valid From'),
                'label' => __('Valid From'),
                'date_format' => 'yyyy-MM-dd',
                'time_format' => 'HH:mm:ss'
            ]
        );
        $elements['valid_to'] = $fieldset->addField(
            'valid_to',
            'date',
            [
                'name' => 'valid_to',
                'title' => __('Valid To'),
                'label' => __('Valid To'),
                'date_format' => 'yyyy-MM-dd',
                'time_format' => 'HH:mm:ss	'
            ]
        );
        $elements['display_pages'] = $fieldset->addField(
            'display_pages',
            'multiselect',
            [
                'title' => __('Display Pages'),
                'label' => __('Display Pages'),
                'name' => 'display_pages',
                "values" => [
                    ["value" => 1, "label" => __("CMS home page")],
                    ["value" => 2, "label" => __("Online shop home page")],
                ]
            ]
        );

        $elements['order'] = $fieldset->addField(
            'order',
            'text',
            [
                'name' => 'order',
                'label' => __('Order'),
                'title' => __('Order'),
            ]
        );

        $this->_eventManager->dispatch('adminhtml_cms_page_edit_tab_main_prepare_form', ['form' => $form]);
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
