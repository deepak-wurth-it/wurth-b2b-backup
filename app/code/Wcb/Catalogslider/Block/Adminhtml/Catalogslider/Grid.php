<?php
namespace Wcb\Catalogslider\Block\Adminhtml\Catalogslider;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Wcb\Catalogslider\Model\catalogsliderFactory
     */
    protected $_catalogsliderFactory;

    /**
     * @var \Wcb\Catalogslider\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Wcb\Catalogslider\Model\catalogsliderFactory $catalogsliderFactory
     * @param \Wcb\Catalogslider\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Wcb\Catalogslider\Model\CatalogsliderFactory $CatalogsliderFactory,
        \Wcb\Catalogslider\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_catalogsliderFactory = $CatalogsliderFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_catalogsliderFactory->create()->getCollection();
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'title',
            [
                'header' => __('Title'),
                'index' => 'title',
            ]
        );
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
            ]
        );
				
				$this->addColumn(
					'valid_from',
					[
						'header' => __('Banner Valid From'),
						'index' => 'valid_from',
						'type'      => 'datetime',
					]
				);

					
				$this->addColumn(
					'valid_to',
					[
						'header' => __('Banner Valid To'),
						'index' => 'valid_to',
						'type'      => 'datetime',
					]
				);

					
				$this->addColumn(
					'sort_order',
					[
						'header' => __('Banner Sort Order'),
						'index' => 'sort_order',
					]
				);
				
        $this->addColumn(
            'edit',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => [
                            'base' => '*/*/edit'
                        ],
                        'field' => 'id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );
		

		
		   $this->addExportType($this->getUrl('catalogslider/*/exportCsv', ['_current' => true]),__('CSV'));
		   $this->addExportType($this->getUrl('catalogslider/*/exportExcel', ['_current' => true]),__('Excel XML'));

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

	
    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {

        $this->setMassactionIdField('id');
        //$this->getMassactionBlock()->setTemplate('Wcb_Catalogslider::catalogslider/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('catalogslider');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('catalogslider/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_status->getOptionArray();

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('catalogslider/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => $statuses
                    ]
                ]
            ]
        );


        return $this;
    }
		

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('catalogslider/*/index', ['_current' => true]);
    }

    /**
     * @param \Wcb\Catalogslider\Model\catalogslider|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		
        return $this->getUrl(
            'catalogslider/*/edit',
            ['id' => $row->getId()]
        );
		
    }

	
		static public function getOptionArray7()
		{
            $data_array=array(); 
			$data_array=['Auto(A)', 'Cargo(C)', 'Drvo(D)', 'Građevina(G)', 'Metal(M)', 'Industry(I)', 'Trade(T)', 'Auto Trade(B)', 'Others'];
            return($data_array);
		}
		static public function getValueArray7()
		{
            $data_array=array();
			foreach(\Wcb\Catalogslider\Block\Adminhtml\Catalogslider\Grid::getOptionArray7() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);
			}
            return($data_array);

		}
		

}