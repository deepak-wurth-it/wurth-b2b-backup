<?php

namespace Wcb\Catalogslider\Block\Adminhtml\Catalogslider\Edit\Tab;

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
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Wcb\Catalogslider\Model\Status $status,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \Wcb\Catalogslider\Model\BlogPosts */
        $model = $this->_coreRegistry->registry('catalogslider');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
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
				"values"    => [
                    ["value" => '_self',"label" => __("Same Page")],
                    ["value" => '_blank',"label" => __("New Tab")],
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
                "values"    => [
                    ["value" => '1',"label" => __("Enabled")],
                    ["value" => '0',"label" => __("Disabled")],
                ]
            ]
        );
						
        $dateFormat = $this->_localeDate->getDateFormat(
            \IntlDateFormatter::MEDIUM
        );
        $timeFormat = $this->_localeDate->getTimeFormat(
            \IntlDateFormatter::MEDIUM
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


						

        $dateFormat = $this->_localeDate->getDateFormat(
            \IntlDateFormatter::MEDIUM
        );
        $timeFormat = $this->_localeDate->getTimeFormat(
            \IntlDateFormatter::MEDIUM
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
						

        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Item Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
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

    public function getTargetOptionArray(){
    	return array(
    				'_self' => "Self",
					'_blank' => "New Page",
    				);
    }
}
