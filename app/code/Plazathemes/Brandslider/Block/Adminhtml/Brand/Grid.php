<?php
/**
* Copyright © 2015 PlazaThemes.com. All rights reserved.

* @author PlazaThemes Team <contact@plazathemes.com>
*/

namespace Plazathemes\Brandslider\Block\Adminhtml\Brand;

use Plazathemes\Brandslider\Model\Status;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended {
	/**
	 * brand factory
	 * @var \Plazathemes\Brandslider\Model\BrandFactory
	 */
	protected $_brandFactory;

	/**
	 * Registry object
	 * @var \Magento\Framework\Registry
	 */
	protected $_coreRegistry;

	/**
	 * [__construct description]
	 * @param \Magento\Backend\Block\Template\Context     $context       [description]
	 * @param \Magento\Backend\Helper\Data                $backendHelper [description]
	 * @param \Plazathemes\Brandslider\Model\BrandFactory $brandFactory [description]
	 * @param \Magento\Framework\Registry                 $coreRegistry  [description]
	 * @param array                                       $data          [description]
	 */
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Backend\Helper\Data $backendHelper,
		\Plazathemes\Brandslider\Model\BrandFactory $brandFactory,
		\Magento\Framework\Registry $coreRegistry,
		array $data = []
	) {
		$this->_brandFactory = $brandFactory;
		$this->_coreRegistry = $coreRegistry;
		parent::__construct($context, $backendHelper, $data);
	}

	protected function _construct() {
		parent::_construct();
		$this->setId('brandGrid');
		$this->setDefaultSort('brand_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);
	}

	protected function _prepareCollection() {
		$storeViewId = $this->getRequest()->getParam('store');
		$collection = $this->_brandFactory->create()->getCollection()->setStoreViewId($storeViewId);
		
		$collection->getSelect();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	/**
	 * @return $this
	 */
	protected function _prepareColumns() {
		$this->addColumn(
			'brand_id',
			[
				'header' => __('Brand ID'),
				'type' => 'number',
				'index' => 'brand_id',
				'header_css_class' => 'col-id',
				'column_css_class' => 'col-id',
			]
		);
		$this->addColumn(
			'title',
			[
				'header' => __('Title'),
				'index' => 'title',
				'class' => 'xxx',
				'width' => '50px',
			]
		);
		$this->addColumn(
			'image',
			[
				'header' => __('Image'),
				'class' => 'xxx',
				'width' => '50px',
				'filter' => false,
				'renderer' => 'Plazathemes\Brandslider\Block\Adminhtml\Brand\Helper\Renderer\Image',
			]
		);
		$this->addColumn(
			'link',
			[
				'header' => __('Link'),
				'index' => 'link',
				'class' => 'xxx',
				'width' => '50px',
			]
		);

		$this->addColumn(
			'status',
			[
				'header' => __('Status'),
				'index' => 'status',
				'type' => 'options',
				'options' => Status::getAvailableStatuses(),
			]
		);
		$this->addColumn(
			'edit',
			[
				'header' => __('Edit'),
				'type' => 'action',
				'getter' => 'getId',
				'actions' => [
					[
						'caption' => __('Edit'),
						'url' => ['base' => '*/*/edit'],
						'field' => 'brand_id',
					],
				],
				'filter' => false,
				'sortable' => false,
				'index' => 'stores',
				'header_css_class' => 'col-action',
				'column_css_class' => 'col-action',
			]
		);
		$this->addExportType('*/*/exportCsv', __('CSV'));
		$this->addExportType('*/*/exportXml', __('XML'));
		$this->addExportType('*/*/exportExcel', __('Excel'));

		return parent::_prepareColumns();
	}

	/**
	 * @return $this
	 */
	protected function _prepareMassaction() {
		$this->setMassactionIdField('brand_id');
		$this->getMassactionBlock()->setFormFieldName('brand');

		$this->getMassactionBlock()->addItem(
			'delete',
			[
				'label' => __('Delete'),
				'url' => $this->getUrl('brandslideradmin/*/massDelete'),
				'confirm' => __('Are you sure?'),
			]
		);

		$statuses = Status::getAvailableStatuses();

		array_unshift($statuses, ['label' => '', 'value' => '']);
		$this->getMassactionBlock()->addItem(
			'status',
			[
				'label' => __('Change status'),
				'url' => $this->getUrl('brandslideradmin/*/massStatus', ['_current' => true]),
				'additional' => [
					'visibility' => [
						'name' => 'status',
						'type' => 'select',
						'class' => 'required-entry',
						'label' => __('Status'),
						'values' => $statuses,
					],
				],
			]
		);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getGridUrl() {
		return $this->getUrl('*/*/grid', ['_current' => true]);
	}
	public function getRowUrl($row) {
		return $this->getUrl(
			'*/*/edit',
			['brand_id' => $row->getId()]
		);
	}
}
