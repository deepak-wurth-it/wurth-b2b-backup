<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Plazathemes\Hozmegamenu\Block\Adminhtml\Hozmegamenu\Edit\Tab;

/**
 * Cms page edit form main tab
 */
class Hozmegamenu extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Hozmegamenu\Context $context
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
        array $data = array()
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /* @var $model \Magento\Cms\Model\Page */
		$model = $this->_coreRegistry->registry('hozmegamenu');
		$isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('Template')));

	   if ($model->getId()) {
            $fieldset->addField('hozmegamenu_id', 'hidden', array('name' => 'hozmegamenu_id'));
        }

        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Enable Module'),
                'title' => __('Enable Module'),
                'name' => 'status',
                'required' => true,
                'options' => [1 => __('Yes'), 0 => __('No')]
            ]
        );
		
		 $fieldset->addField(
            'is_home',
            'select',
            [
                'label' => __('Show Homepage'),
                'title' => __('Show Homepage'),
                'name' => 'is_home',
                'required' => true,
                'options' => [1 => __('Yes'), 2 => __('No')]
            ]
        );
		
		// $fieldset->addField(
            // 'show_mobile',
            // 'select',
            // [
                // 'label' => __('Show on mobile'),
                // 'title' => __('Show on mobile'),
                // 'name' => 'show_mobile',
                // 'required' => true,
                // 'options' => [1 => __('Yes'), 2 => __('No')]
            // ]
        // );
		
		$fieldset->addField(
            'type_menu',
            'select',
            [
                'label' => __('Type Menu'),
                'title' => __('Type Menu'),
                'name' => 'type_menu',
                'required' => true,
                'options' => [1 => __('Hoztical'), 2 => __('Vertical')]
            ]
        );
		
		
		$fieldset->addField(
			'is_new',
			'text',
			[
				'title' => __('New'),
				'label' => __('New'),
				'name' => 'is_new',
			]
		);

		$fieldset->addField(
			'is_sale',
			'text',
			[
				'title' => __('Hot'),
				'label' => __('Hot'),
				'name' => 'is_sale',
			]
		);
		
				
		$fieldset->addField(
			'is_level',
			'text',
			[
				'title' => __('Show Level'),
				'label' => __('Show Level'),
				'name' => 'is_level',
			]
		);

		$fieldset->addField(
			'is_column',
			'text',
			[
				'title' => __('Number columns'),
				'label' => __('Number columns'),
				'name' => 'is_column',
			]
		);

		if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '2' : '1');
        }


        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
		
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Basic Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Basic Information');
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
}
