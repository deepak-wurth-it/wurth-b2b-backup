<?php 
namespace Wcb\Megamenu\Block;

use Magento\Catalog\Model\Category;
use Magento\Customer\Model\Context;
class Menu extends \Magento\Framework\View\Element\Template 
{
    /**
     * @var Category
     */
    protected $_categoryInstance;
	protected $_categoryInstance1;
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
        \Magento\Cms\Api\PageRepositoryInterface $pageRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
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
        $this->_cmsPage = $pageRepository;
        $this->_search = $searchCriteriaBuilder;
        $this->_filterBuilder = $filterBuilder;
        parent::__construct($context, $data);
		
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
		$is_link = '';//$this->getConfig('is_link'); 
		$arr_catsid = [''];//json_decode($is_link); 
		

        $html[] = '<div class="parentMenu" style="">';
		if(in_array($id,$arr_catsid)) {
			$html[] = '<a href="'.$this->getBaseUrl().'" class="pt_cate '.$is_active.'">';
		} else {
			$html[] = '<a href="'.$link.'" class="pt_cate '.$is_active.'">';
		}
        $name = $category->getName();
        $html[] = '<span>' . $name . '</span>';
		$html[]= $is_sale; 
		$html[]= $is_new;
		if(file_exists($thumb_nail)) {
			$html[]= '<img width="24" height="24" src="'.$thumb_nail.'" alt="Thumbnail" />';
		}
        $html[] = '</a>';
        $html[] = '</div>';
		
        
        // --- Add Popup block (hidden) ---
        if ($drawPopup==100)
        {
            // --- Popup function for hide ---
            $html[] = '<div id="popup' . $id . '"  class="popup" style="display: none; width: 100%;">';
            // --- draw Sub Categories ---
            if (count($activeChildren))
            {
                for ($x = 0; $x <= 36; $x++) {

                    $bId = floor($x/6 +1);
                    

                $html[] = '<div class="col-md-4 block'.$bId.'" id="block'.$bId.'' . $id . '">';
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
        $html[] = '<div class="country-img" style="float: right;"><img src="'.$this->getViewFileUrl('images/flag-icon.png').'" alt="Demo">'.  __('Croatia').' </div>';
        $html = implode("\n", $html);
        return $html;
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
		$arr_catsid = [''];//json_decode($is_link); 
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
                $imageUrl = $child1->getThumbNail();
				$is_sale = null; 
				$is_new = null; 
				if($child1->getIsSale()==1) {
					$is_sale = '<span class="is_sale">'.$this->getConfig('is_sale').'</span>';
				}
				if($child1->getIsNew()==1) {
					$is_new = '<span class="is_new">'.$this->getConfig('is_new').'</span>';
				}	
				$sub_link =  $this->_catalogCategory->getCategoryUrl($child);

				$html.= '<div class="category-img" style="float: left;"><img src="'.$imageUrl.'" alt="Category"  width="24px" height="24px"> </div>';
                    
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

    public function CmsLink($id,$is_device = null) {
        $storeId = $this->_storeManager->getStore()->getId();
        $blockData = $this->_pageFactory->setStoreId($storeId)->load($id);
        $link = $this->_storeManager->getStore()->getBaseUrl().$blockData->getIdentifier();
        $currentUrl =  $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        $active = '';
        if ($currentUrl == $link) {
            $active = ' act';
        }
        $html = "";

        if($is_device == 'mobile') {
            $html ='<li><a href="'.$link.'"><span class="name">'.$blockData->getTitle().'</span></a></li>';
            
        } else {
            $html ='<div class="pt_menu nav-1 '.$active.'" id="pt_cms">
                <div class="parentMenu"><a href="'.$link.'"><span>'.$blockData->getTitle().'</span></a></div>
            </div>';
        }
        return $html;
    
}


    public function getItemsActive() {
        return $this->toOptionArray(); 
	}
    /**
     * Function toOptionArray()
     * 
     * Used to get the list of enabled and addinmenu attribute set filter
     * 
     * @return Array
     */
	public function toOptionArray()
    {
        $root_cat = $this->getRootCategory()->getData();
        $pages = preg_filter("/^/", 'category_', array_column($root_cat, 'entity_id'));
        foreach($this->_cmsPage->getList($this->_getSearchCriteria())->getItems() as $page) {
            $pages[] = 'cms_'.$page->getId();
        }
        return json_encode($pages);
    }
        /**
     * Function getRootCategory()
     * 
     * Used to get level 2 category collection
     * 
     * @return Array
     */
    public function getRootCategory()
    {
        return $this->getCategoryLevel2(1);
    }
    
    /**
     * Function _getSearchCriteria()
     * 
     * Used to filter cms page collection
     * 
     * @return Array
     */
    protected function _getSearchCriteria()
    {
        return $this->_search->addFilter('is_active', '1')->addFilter('addintomenu', '1')->create();
    }
	public function getCategoryLevel2($level = 2) {
	
        $collection = $this->_categoryInstance->getCollection()
                           ->addAttributeToSelect('*')
                           -> addAttributeToFilter('level', $level)
                           -> addAttributeToFilter('is_active',1)
                           -> addAttributeToFilter('include_in_menu',1);
        return $collection ; 
}

protected function getActiveChildren($parent, $level)
{
    $activeChildren = array();
    // --- check level ---
    $maxLevel = $this->getConfig('is_level');
    if(!$maxLevel) $maxLevel = 2;
    if ($maxLevel > 0)
    {
        if ($level >= ($maxLevel - 1)) return $activeChildren;
    }
    $childs = $parent->getChildrenCategories(); 
    
    if (count($childs))
    {
        foreach ($childs as $child)	
        {
            $childCateg = $this->_categoryInstance1->create() ->load($child->getId());
            if ($child->getIsActive() && $childCateg->getIncludeInMenu())
            {
                array_push($activeChildren, $child);
            }
        }
    }
    return $activeChildren;
}

}
