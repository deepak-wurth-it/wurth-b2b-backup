<?php
/**
* Copyright © 2015 PlazaThemes.com. All rights reserved.

* @author PlazaThemes Team <contact@plazathemes.com>
*/

namespace Plazathemes\Brandslider\Block\Adminhtml\Brand\Edit\Tab;

use Plazathemes\Brandslider\Model\Status;

class Brand extends \Magento\Backend\Block\Widget\Form\Generic
 implements \Magento\Backend\Block\Widget\Tab\TabInterface {
	/**
	 * @var \Magento\Framework\ObjectFactory
	 */
	protected $_objectFactory;

	/**
	 * @var \Magento\Store\Model\System\Store
	 */
	protected $_systemStore;

	/**
	 * helper
	 * @var \Plazathemes\Brandslider\Helper\Data
	 */
	protected $_brandsliderHelper;

	/**
	 * @var \Plazathemes\Brandslider\Model\Brand
	 */
	protected $_brand;

	/**
	 * [__construct description]
	 * @param \Magento\Backend\Block\Template\Context    $context            [description]
	 * @param \Plazathemes\Brandslider\Helper\Data        $brandsliderHelper [description]
	 * @param \Magento\Framework\Registry                $registry           [description]
	 * @param \Magento\Framework\Data\FormFactory        $formFactory        [description]
	 * @param \Magento\Store\Model\System\Store          $systemStore        [description]
	 * @param \Magento\Framework\ObjectFactory           $objectFactory      [description]
	 * @param \Plazathemes\Brandslider\Model\Brand       $brand             [description]
	 * @param array                                      $data               [description]
	 */
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Plazathemes\Brandslider\Helper\Data $brandsliderHelper,
		\Magento\Framework\Registry $registry,
		\Magento\Framework\Data\FormFactory $formFactory,
		\Magento\Store\Model\System\Store $systemStore,
		\Plazathemes\Brandslider\Model\Brand $brand,
		array $data = []
	) {
		$this->_localeDate = $context->getLocaleDate();
		$this->_systemStore = $systemStore;
		$this->_brandsliderHelper = $brandsliderHelper;
		$this->_brand = $brand;
		parent::__construct($context, $registry, $formFactory, $data);
	}

	protected function _prepareLayout() {
		$this->getLayout()->getBlock('page.title')->setPageTitle($this->getPageTitle());

		\Magento\Framework\Data\Form::setFieldsetElementRenderer(
			$this->getLayout()->createBlock(
				'Plazathemes\Brandslider\Block\Adminhtml\Form\Renderer\Fieldset\Element',
				$this->getNameInLayout() . '_fieldset_element'
			)
		);
	}

	/**
	 * Prepare form
	 *
	 * @return $this
	 */
	protected function _prepareForm() {
		$model = $this->_coreRegistry->registry('brand');

		// $storeViewId = $this->getRequest()->getParam('store');
		
		/** @var \Magento\Framework\Data\Form $form */
		$form = $this->_formFactory->create();

		$form->setHtmlIdPrefix($this->_brand->getFormFieldHtmlIdPrefix());

		$fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Brand Information')]);

		if ($model->getId()) {
			$fieldset->addField('brand_id', 'hidden', ['name' => 'brand_id']);
		}

		$elements = [];
		$elements['title'] = $fieldset->addField(
			'title',
			'text',
			[
				'name' => 'title',
				'label' => __('Title'),
				'title' => __('Title'),
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
		
		$elements['status'] = $fieldset->addField(
			'status',
			'select',
			[
				'label' => __('Status'),
				'title' => __('Brand Status'),
				'name' => 'status',
				'options' => Status::getAvailableStatuses(),
			]
		);

		$elements['link'] = $fieldset->addField(
			'link',
			'text',
			[
				'title' => __('Link'),
				'label' => __('Link'),
				'name' => 'link',
			]
		);

		$elements['image'] = $fieldset->addField(
			'image',
			'image',
			[
				'title' => __('Brand Image'),
				'label' => __('Brand Image'),
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

	public function getBrand() {
		return $this->_coreRegistry->registry('brand');
	}

	public function getPageTitle() {
		// return $this->getBrand()->getId() ? __("Edit Brand '%1'", $this->escapeHtml($this->getBrand()->getName())) : __('New Brand');
	}

	/**
	 * Prepare label for tab
	 *
	 * @return string
	 */
	public function getTabLabel() {
		return __('Brand Information');
	}

	/**
	 * Prepare title for tab
	 *
	 * @return string
	 */
	public function getTabTitle() {
		return __('Brand Information');
	}

	/**
	 * {@inheritdoc}
	 */
	public function canShowTab() {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isHidden() {
		return false;
	}

	/**
	 * Check permission for passed action
	 *
	 * @param string $resourceId
	 * @return bool
	 */
	protected function _isAllowedAction($resourceId) {
		return $this->_authorization->isAllowed($resourceId);
	}
}
