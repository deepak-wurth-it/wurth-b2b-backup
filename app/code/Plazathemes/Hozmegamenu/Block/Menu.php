<?php 
namespace Plazathemes\Hozmegamenu\Block;

use Magento\Catalog\Model\Category;
use Magento\Customer\Model\Context;
class Menu	 extends \Magento\Framework\View\Element\Template 
{
    /**
     * @var Category
     */
    protected $_categoryInstance;
	protected $_categoryInstance1;

    /**
     * Current category key
     *
     * @var string
     */
    protected $_currentCategoryKey;

    /**
     * Array of level position counters
     *
     * @var array
     */
    protected $_itemLevelPositions = [];

    /**
     * Catalog category
     *
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_catalogCategory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * Customer session
     *
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\Resource\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected $flatState;
	
	protected $_blockFactory;
	protected $_pageFactory;
	protected $_menuFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\Resource\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Catalog\Helper\Category $catalogCategory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\Indexer\Category\Flat\State $flatState
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Catalog\Helper\Category $catalogCategory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $flatState,
		\Magento\Cms\Model\BlockFactory $blockFactory,
		\Magento\Cms\Model\PageFactory $pageFactory,
		\Plazathemes\Hozmegamenu\Model\HozmegamenuFactory $menuFactory,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogLayer = $layerResolver->get();
        $this->httpContext = $httpContext;
        $this->_catalogCategory = $catalogCategory;
        $this->_registry = $registry;
        $this->flatState = $flatState;
        $this->_categoryInstance = $categoryFactory->create();
		$this->_categoryInstance1 = $categoryFactory;
		$this->_blockFactory = $blockFactory->create();
		$this->_pageFactory = $pageFactory -> create();
		$this->_menuFactory = $menuFactory;
        parent::__construct($context, $data);
		
    }
	 public function _prepareLayout()
    { 
		$this->getStaticBlockFromIdentify(); 
		
	}
	
	public function customLink($id,$is_device=null) {
		return $this->drawCustomMenuBlock($id,$is_device);
						
	}
	
	public function CmsLink($id,$is_device = null) {
			$storeId = $this->_storeManager->getStore()->getId();
			$blockData = $this->_pageFactory->setStoreId($storeId)->load($id);
			$link = $this->_storeManager->getStore()->getBaseUrl().$blockData->getIdentifier();
			$html = "";

			if($is_device == 'mobile') {
				$html ='<li><a href="'.$link.'"><span class="name">'.$blockData->getTitle().'</span></a></li>';
				
			} else {
				$html ='<div class="pt_menu nav-1" id="pt_cms">
					<div class="parentMenu"><a href="'.$link.'"><span>'.$blockData->getTitle().'</span></a></div>
				</div>';
			}
			return $html;
		
	}
	
	public function getConfig($value='enabled'){
 
		//$config =  $this->_scopeConfig->getValue('hozmegamenu/active_display/'.$value, \Magento\Store\Model\ScopeInterface::SCOPE_STORE); 
		$id = 1;
		$store = $this->_storeManager->getStore()->getId();
		
        $model = $this->_menuFactory->create();
		
		// $collection = $model->getCollection();
		
		if($store)
		{
			$collection = $model->getCollection()->addFieldToFilter('store', $store);
			if(count($collection)<= 0)
				$collection = $model->getCollection()->addFieldToFilter('store', '0');
		}
		else
			$collection = $model->getCollection()->addFieldToFilter('store', '0');
		
		foreach($collection as $item) {
			$id = $item['hozmegamenu_id'];
		}
		$config = $model->load($id); 
	
		   
		 return $config->getData($value); 
		 
	}
	
	public function getItemsActive() {
		return $this->getConfig('items'); 
	}
	
	public function renderMenu() {
		$categories = $this->getCategoryLevel2();
		$html = array();
		$item = 0;
		foreach($categories as $category) { $item ++;
			$html[] = $this->drawCustomMenuItem($category,0,false,$item);
		}
	
		return $html; 
		
	}
	
	public function getCategoryLevel2() {
	
		    $collection = $this->_categoryInstance->getCollection()
							   ->addAttributeToSelect('*')
							   -> addAttributeToFilter('level',2)
							   -> addAttributeToFilter('is_active',1);
			return $collection ; 
	}
	
	public function AllgetCategories() {
		
		$categoryLevel2 = $this->getCategoryLevel2();
			$arrayCates = array(); 
		 foreach($categoryLevel2 as $cate) {
			 $cateChild = $cate->getChildrenCategories();
			 $arrayCates[$cate->getId()]  = $cateChild;
	
		 }
		 return $arrayCates;
	
	}
	
	
	function partition_element(Array $list, $p) {
		$listlen = count($list);
		$partlen = floor($listlen / $p);
		$partrem = $listlen % $p;
		$partition = array();
		$mark = 0;
		for($px = 0; $px < $p; $px ++) {
			$incr = ($px < $partrem) ? $partlen + 1 : $partlen;
			$partition[$px] = array_slice($list, $mark, $incr);
			$mark += $incr;
		}
		return $partition;
	}
	
	 protected function getActiveChildren($parent, $level)
    {
        $activeChildren = array();
        // --- check level ---
        $maxLevel = $this->getConfig('is_level');
		if(!$maxLevel) $maxLevel = 3;
        if ($maxLevel > 0)
        {
            if ($level >= ($maxLevel - 1)) return $activeChildren;
        }
        $childs = $parent->getChildrenCategories(); 
        if (count($childs))
        {
            foreach ($childs as $child)	
            {
				
                if ($child->getIsActive())
                {
                    array_push($activeChildren, $child);
                }
            }
        }
        return $activeChildren;
    }

    private function explodeByColumns($target, $num)
    {
        $countChildren = 0;
        foreach ($target as $cat => $childCat)
        {
            $activeChildCat = $this->getActiveChildren($childCat, 0);
            if($activeChildCat){
                $countChildren++;
            }
        }
        // if($countChildren == 0){ 
            // $num = 3; 
        // }
        $count = count($target);
       
        if ($count) 
        $target =  $this->partition_element($target, $num);
       
        return $target;
    }
	
	   public function isCategoryActive($category)
    {
        if ($this->getCurrentCategory()) {
            return in_array($category->getId(), $this->getCurrentCategory()->getPathIds());
        }
        return false;
    }
	
	 public function getStaticBlockFromIdentify($condition = null) {
		
		 $storeId = $this->_storeManager->getStore()->getId();
		
		 $blocks = $this->_blockFactory->setStoreId($storeId)->getCollection()
						->addFieldToFilter('identifier', array('like'=>'pt_item_menu'.'%'))
						->addFieldToFilter('is_active', 1);									
		 return $blocks; 
         
	}
	
	public function drawCustomMenuBlock($blockId,$is_device ='mobile')
    {
		$storeId = $this->_storeManager->getStore()->getId();
		 
        $html = array();
        // --- Static Block ---
        //$blockId = sprintf('pt_custommenu_%d', $id); // --- static block key
        $block = $this->_blockFactory->setStoreId($storeId)
            ->load($blockId);
        //$title = $block->getTitle();
        $id = '_'.$blockId;
        //echo $isActive = $block->getIsActive();die();
        
        $blockHtml = $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId($blockId)->toHtml();
        $drawPopup = $blockHtml;
        if ($drawPopup)
        {
            $html[] = '<div id="pt_menu' . $id . '" class="pt_menu">';
        }
        else
        {
            $html[] = '<div id="pt_menu' . $id . '" class="pt_menu">';
        }
        // --- Top Menu Item ---
        $html[] = '<div class="parentMenu">';

        $name = $block->getTitle();
        $html[] = '<span class="block-title">' . $name . '</span>';
        $html[] = '</div>';
        // --- Add Popup block (hidden) ---
        if ($drawPopup)
        {
            // --- Popup function for hide ---
            $html[] = '<div id="popup' . $id . '" class="popup cmsblock" style="display: none; width: 904px;">';
            if ($blockHtml)
            {
                $html[] = '<div class="block2" id="block2' . $id . '">';
                $html[] = $blockHtml;
                $html[] = '</div>';
            }
            $html[] = '</div>';
        }
        $html[] = '</div>';
        $html = implode("\n", $html);
		
		if($is_device == 'mobile') {
				$html = '';
				$html ='<li><a href="#"><span class="name">'. $name .'</span></a>';
				 if ($drawPopup)
						{
							// --- Popup function for hide ---
							$html.= '<div id="popup_mobile' . $id . '" class="popup cmsblock_mobile" style="display: none; width: 904px;">';
							if ($blockHtml)
							{
								$html.= '<div class="block2" id="block2' . $id . '">';
								$html.= $blockHtml;
								$html.= '</div>';
							}
							$html .= '</div>';
						}
				$html .= '</li>';
				
		}
		
        return $html;
    }
	
	function getThumbUrl($thumb=null) {
		
		return   $this->_storeManager->getStore()->getBaseUrl(
					\Magento\Framework\UrlInterface::URL_TYPE_MEDIA
				) . 'catalog/category/' . $thumb;
	}
	

	
	 public function drawCustomMenuItem($category= null, $level = 0, $last = false,$item= null )
    {
        if (!$category) return ''; 
		$category = $this->_categoryInstance1->create() ->load($category);
        $html = array();
        $blockHtml = '';
        $id = $category->getId();
        // --- Static Block ---
        $blockId = sprintf('pt_menu_idcat_%d', $id); // --- static block key
        $blockHtml = $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId($blockId)->toHtml();;
        /*check block right*/
        $blockIdRight = sprintf('pt_menu_idcat_%d_right', $id); // --- static block key
        $blockHtmlRight = $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId($blockIdRight)->toHtml();
        if($blockHtmlRight) $blockHtml = $blockHtmlRight;
        // --- Sub Categories ---
        $activeChildren = $this->getActiveChildren($category, $level); 
        // --- class for active category ---
        $active = ''; //if ($this->isCategoryActive($category)) $active = ' act';
        // --- Popup functions for show ---
        $drawPopup = ($blockHtml || count($activeChildren));
        if ($drawPopup)
        {
            $html[] = '<div id="pt_menu' . $id . '" class="pt_menu' . $active . ' nav-' .$item. '">';
        }
        else
        {
            $html[] = '<div id="pt_menu' . $id . '" class="pt_menu' . $active . ' nav-' .$item. ' pt_menu_no_child">';
        }
		$is_sale = null; 
		$is_new = null; 
		if($category->getIsSale()==1) {
			$is_sale = '<span class="is_sale">'.$this->getConfig('is_sale').'</span>';
		}
		if($category->getIsNew()==1) {
			$is_new = '<span class="is_new">'.$this->getConfig('is_new').'</span>';
		}
		$thumb_nail = $this->getThumbUrl($category->getThumbNail());
		$bg_img = $this->getConfig('image');
		$bg_category = json_decode($bg_img);
        // --- Top Menu Item ---
		$link =  $this->_catalogCategory->getCategoryUrl($category);
		
		$is_active = $this->_catalogLayer->getCurrentCategory()->getId(); 
		if($is_active == $id) {
			$is_active = 'act';
		} else {
			$is_active = null;
		}
		
		$arr_catsid =array();
		$is_link = $this->getConfig('is_link'); 
		$arr_catsid = json_decode($is_link); 
		

        $html[] = '<div class="parentMenu" style="">';
		if(in_array($id,$arr_catsid)) {
			$html[] = '<a href="#" class="pt_cate '.$is_active.'">';
		} else {
			$html[] = '<a href="'.$link.'" class="pt_cate '.$is_active.'">';
		}
        $name = $category->getName();
        $html[] = '<span>' . $name . '</span>';
		$html[]= $is_sale; 
		$html[]= $is_new;
		if(file_exists($thumb_nail)) {
			$html[]= '<img width="50" height="50" src="'.$thumb_nail.'" alt="Thumbnail" />';
		}
        $html[] = '</a>';
        $html[] = '</div>';
		
        
        // --- Add Popup block (hidden) ---
        if ($drawPopup==100)
        {
            // --- Popup function for hide ---
            $html[] = '<div id="popup' . $id . '"  class="popup" style="display: none; width: 1228px;">';
            // --- draw Sub Categories ---
            if (count($activeChildren))
            { 
                $html[] = '<div class="block1" id="block1' . $id . '">';
                $html[] = $this->drawColumns($activeChildren, $id);
                if ($blockHtml && $blockHtmlRight)
                {
                    $html[] = '<div class="column blockright last">';
                    $html[] = $blockHtml;
                    $html[] = '</div>';
                }
                $html[] = '<div class="clearBoth"></div>';
                $html[] = '</div>';
            }
            // --- draw Custom User Block ---
            if ($blockHtml && !$blockHtmlRight)
            {
                $html[] = '<div class="block2" id="block2' . $id . '">';
                $html[] = $blockHtml;
                $html[] = '</div>';
            }
            $html[] = '</div>';
        }
        $html[] = '</div>';
        $html = implode("\n", $html);

        return $html;
    }
	
	
	public function drawColumns($children, $id)
    {
        $html = '';
        // --- explode by columns ---
        $columns = $this->getConfig('is_column');
        if ($columns < 1) $columns = 1;
        $chunks = $this->explodeByColumns($children, $columns);
        $columChunk = count($chunks);
        // --- draw columns ---
        $classSpecial = '';
        $keyLast = 0;
        foreach ($chunks as $key => $value){
            if(count($value)) $keyLast++;
        }
        $blockHtml = '';
        $blockId = sprintf('pt_menu_idcat_%d', $id); // --- static block key
        $blockHtml = $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId($blockId)->toHtml();
        /*Check blog right*/
        $blockIdRight = sprintf('pt_menu_idcat_%d_right', $id); // --- static block key
        $blockHtmlRight = $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId($blockIdRight)->toHtml();
        if($blockHtmlRight) $blockHtml = $blockHtmlRight;
        foreach ($chunks as $key => $value)
        {
            if (!count($value)) continue;
            if($key == $keyLast - 1){
                $classSpecial = ($blockHtmlRight && $blockHtml)? '':' last';
            }elseif($key == 0){
                $classSpecial = ' first';
            }else{
                $classSpecial = '';
            }
            $html.= '<div class="column'. $classSpecial . ' col' . ($key+1) . '">';
            $html.= $this->drawMenuItem($value, 1, $columChunk);
            $html.= '</div>';
        }
        return $html;
    }
	
	 public function drawMenuItem($children, $level = 1, $columChunk= NULL) {

        $html = '<div class="itemMenu level' . $level . '">';
        $keyCurrent =  $this->_catalogLayer->getCurrentCategory()->getId();
        $countChildren = 0;
        $ClassNoChildren = '';
        foreach ($children as $child)
        {
            $activeChildCat = $this->getActiveChildren($child, 0);
            if($activeChildCat){
                $countChildren++;
            }
        }
        if($countChildren == 0 && $columChunk == 1){ 
            $ClassNoChildren = ' nochild'; 
        }
        $arr_catsid =array();
		$is_link = $this->getConfig('is_link'); 
		$arr_catsid = json_decode($is_link); 
		//$arr_catsid = explode(',',$this->getConfig('is_link'));
        foreach ($children as $child)
        {
           // echo "<pre>"; print_r($child->getData()); echo "</pre>";
            if ($child->getIsActive())
            {
                 // --- class for active category ---
					$active = '';
            
                
                if ($child->getId() == $keyCurrent) $active = ' act';
                
                // --- format category name ---
                $name = $child->getName();
				$child1 = $this->_categoryInstance1->create() ->load($child->getId());
				$is_sale = null; 
				$is_new = null; 
				if($child1->getIsSale()==1) {
					$is_sale = '<span class="is_sale">'.$this->getConfig('is_sale').'</span>';
				}
				if($child1->getIsNew()==1) {
					$is_new = '<span class="is_new">'.$this->getConfig('is_new').'</span>';
				}	
				$sub_link =  $this->_catalogCategory->getCategoryUrl($child);
				
                if( in_array($child->getId(),$arr_catsid) ){
                    $html.= '<h4 class="itemMenuName level' . $level . $active . $ClassNoChildren . '"><span>' . $name . '</span>' . $is_sale.$is_new . '</h4>';
                }else{
                    $html.= '<a class="itemMenuName level' . $level . $active . $ClassNoChildren . '" href="'.$sub_link.'"><span>' . $name . '</span>' . $is_sale.$is_new . '</a>';
                }
                $activeChildren = $this->getActiveChildren($child, $level);
                if (count($activeChildren) > 0)
                { 
                    $html.= '<div class="itemSubMenu level' . $level . '">';
                    $html.= $this->drawMenuItem($activeChildren, $level + 1,$columChunk);
                    $html.= '</div>';
                }
            }
        }
        $html.= '</div>';
        return $html;
    }
	
	public function generateHtmlMobileMenu() {
		
					$items = $this->getItemsActive();
					$itemsActive = json_decode($items); 
					$item = 0;
			
						$id = null; 
						$label = null; 
						$html = null; 
						foreach($itemsActive as $itemActive) { 
								$item++;
								$item1 =  explode( '_', $itemActive ) ;
								$label = $item1[0]; 
								$id = $item1[1];
								 switch ($label) {
									case "cms":							
											$html .= $this->CmsLink($id,'mobile');								
										break;
									case "block":			
											$html .= $this->CustomLink($id,'mobile');
										break;
									case "category":									
										   $html .= $this->categoryMobile($id);					
										break;
									}
						}
						
						return $html ; 
					

	}
	
	public function categoryMobile($id= null) {
	
		if(!$id) return ; 
		$html = null; 
		$category = $this->_categoryInstance1->create() ->load($id);
		$link =  $this->_catalogCategory->getCategoryUrl($category);		
		$html .= '<li class="level1">';
				 $html .='<a href= "'.$link.'"><span class="name">'.$category->getName().'</span></a>';
				 $html .= '<ul class="level2">';
				 $childCate = $category->getChildrenCategories();
				 foreach($childCate as $cate2) {
					 $nameLevel2 = $this->_categoryInstance ->load($cate2->getId())->getName();
					 $html .= '<li>';
					    $link2 =  $this->_catalogCategory->getCategoryUrl($cate2);		
						$html .='<a href= "'.$link2.'"><span class="name">'.$nameLevel2.'</span></a>';
						 $cateChild = $cate2->getChildrenCategories();
						  $html .= '<ul class="level3">';
						 foreach($cateChild as $cate3) {
							  $nameLevel3 = $this->_categoryInstance ->load($cate3->getId())->getName();
							  $link3 =  $this->_catalogCategory->getCategoryUrl($cate3);
							  $html .= '<li>';
									$html .='<a href= "'.$link3.'"><span class="name">'.$nameLevel3.'</span></a>';
							  $html .="</li>"; 			
						 }
						 $html .="</ul>"; 
					 $html .="</li>"; 
				 }
				 $html .="</ul>"; 
		 $html .='</li>';
		 
		return $html; 									
	}
	
	
	
	
	
	
	
	

   
}
