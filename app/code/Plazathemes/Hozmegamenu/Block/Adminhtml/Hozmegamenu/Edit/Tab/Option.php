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
class Option extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    protected $_categoryInstance;

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
		\Magento\Catalog\Model\CategoryFactory $categoryFactory,
		\Magento\Cms\Model\BlockFactory $blockFactory,
		\Magento\Cms\Model\PageFactory $pageFactory,
        array $data = array()
    ) {
        $this->_systemStore = $systemStore;
		$this->_categoryInstance = $categoryFactory->create();
		$this->_blockFactory = $blockFactory->create();
		 $this->_pageFactory = $pageFactory->create();
        parent::__construct($context, $registry, $formFactory, $data);
    }
	
	public function getModelHozmegamenu() {
		$model = $this->_coreRegistry->registry('hozmegamenu');
		return $model; 
	}
	
	
	public function toOptionArray(){
			$collection = $this->_categoryInstance->getCollection()
							   -> addAttributeToFilter('level',2)
								   -> addAttributeToFilter('is_active',1);
			$arr = array();					   
			$i=0;
			foreach($collection as $cate) {
					$category = $this->_categoryInstance ->load($cate->getId());
					$arr[$i]=array('value'=>$category->getId(), 'label'=> $category->getName());
							$i++;
		
			}

			return $arr;

    }
	
	public function getStaticBlockFromIdentify($condition = null) {
		
		 $storeId = $this->_storeManager->getStore()->getId();
		
		 $blocks = $this->_blockFactory->setStoreId($storeId)->getCollection()
						->addFieldToFilter('identifier', array('like'=>$condition.'%'))
						->addFieldToFilter('is_active', 1);		
		$arr = array();		
		$i=0;
		foreach($blocks as $block) {
					$arr[$i]=array('value'=>$block->getId(), 'label'=> $block->getTitle());
							$i++;
		}
						
		 return $arr; 
         
	}
	
	public function getCmsPages($condition = null) {
		
		 $storeId = $this->_storeManager->getStore()->getId();
		
		 $blocks = $this->_pageFactory->setStoreId($storeId)->getCollection()
						->addFieldToFilter('is_active', 1);		
		$arr = array();		
		$i=0;
		foreach($blocks as $block) {
					$arr[$i]=array('value'=>$block->getId(), 'label'=> $block->getTitle());
							$i++;
		}
						
		 return $arr; 
         
	}
	
	public function toEffects() {

		$effects = array(
				0 => 'Slide',
				1 => 'Fade',
				2 => 'None', 
				);
		return $effects;		
		
	}



   

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Main');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Main Information');
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
