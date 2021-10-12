<?php
namespace Wcb\Megamenu\Block;
use Magento\Catalog\Model\Category;
use Magento\Customer\Model\Context;
class Menu extends \Magento\Framework\View\Element\Template {
    /**
     * @var Category
     */
    protected $_categoryInstance;
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
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;
    protected $layerResolver;
    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\Resource\Product\CollectionFactory
     */
    protected $_productCollectionFactory;
    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected $_blockFactory;
    protected $_pageFactory;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\Resource\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Catalog\Helper\Category $catalogCategory
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param \Magento\Cms\Api\PageRepositoryInterface $pageRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\View\Asset\Repository $assetRepos
     * @param \Magento\Catalog\Helper\ImageFactory $helperImageFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Helper\Category $catalogCategory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Cms\Model\BlockFactory $blockFactory,
		\Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Cms\Api\PageRepositoryInterface $pageRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\View\Asset\Repository $assetRepos,
        \Magento\Catalog\Helper\ImageFactory $helperImageFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogLayer = $layerResolver;
        $this->_catalogCategory = $catalogCategory;
        $this->_categoryInstance = $categoryFactory;
        $this->_blockFactory = $blockFactory;
        $this->_pageFactory = $pageFactory;
        $this->_cmsPage = $pageRepository;
        $this->_search = $searchCriteriaBuilder;
        $this->_filterBuilder = $filterBuilder;
        $this->assetRepos = $assetRepos;
        $this->helperImageFactory = $helperImageFactory;
        parent::__construct($context, $data);
    }
    public function _prepareLayout() {
        $this->getStaticBlockFromIdentify();
    }
    public function customLink($id, $is_device = null) {
        return $this->drawCustomMenuBlock($id, $is_device);
    }
    public function CmsLink($id, $is_device = null) {
        $storeId = $this->_storeManager->getStore()->getId();
        $blockData = $this->_pageFactory->create()->setStoreId($storeId)->load($id);
        $link = $this->_storeManager->getStore()->getBaseUrl() . $blockData->getIdentifier();
        $html = "";
        if ($is_device == 'mobile') {
            $html = '<li><a href="' . $link . '"><span class="name">' . $blockData->getTitle() . '</span></a></li>';
        } else {
            $html = '<div class="pt_menu nav-1" id="pt_cms">
					<div class="parentMenu"><a href="' . $link . '"><span>' . $blockData->getTitle() . '</span></a></div>
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
    public function toOptionArray() {
        $root_cat = $this->getRootCategory()->getData();
        $pages = preg_filter("/^/", 'category_', array_column($root_cat, 'entity_id'));
        foreach ($this->_cmsPage->getList($this->_getSearchCriteria())->getItems() as $page) {
            $pages[] = 'cms_' . $page->getId();
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
    public function getRootCategory() {
        return $this->getCategoryLevel2(1);
    }
    /**
     * Function _getSearchCriteria()
     *
     * Used to filter cms page collection
     *
     * @return Array
     */
    protected function _getSearchCriteria() {
        return $this->_search->addFilter('is_active', '1')->addFilter('addintomenu', '1')->create();
    }
    public function getCategoryLevel2($level = 2) {
        $collection = $this->_categoryInstance->create()->getCollection()
                                    ->addAttributeToSelect('*')
                                    ->addAttributeToFilter('level', $level)
                                    ->addAttributeToFilter('is_active', 1)
                                    ->addAttributeToFilter('include_in_menu', 1);
        return $collection;
    }
    public function renderMenu() {
        $categories = $this->getCategoryLevel2();
        $html = array();
        $item = 0;
        foreach ($categories as $category) {
            $item++;
            $html[] = $this->drawCustomMenuItem($category, 0, false, $item);
        }
        return $html;
    }
    public function AllgetCategories() {
        $categoryLevel2 = $this->getCategoryLevel2();
        $arrayCates = array();
        foreach ($categoryLevel2 as $cate) {
            $cateChild = $cate->getChildrenCategories();
            $arrayCates[$cate->getId() ] = $cateChild;
        }
        return $arrayCates;
    }
    function partition_element(Array $list, $p) {
        $listlen = count($list);
        $partlen = floor($listlen / $p);
        $partrem = $listlen % $p;
        $partition = array();
        $mark = 0;
        for ($px = 0;$px < $p;$px++) {
            $incr = ($px < $partrem) ? $partlen + 1 : $partlen;
            $partition[$px] = array_slice($list, $mark, $incr);
            $mark+= $incr;
        }
        return $partition;
    }
    protected function getActiveChildren($parent, $level) {
        $activeChildren = array();
        // --- check level ---
        $maxLevel = 2;
        if ($maxLevel > 0) {
            if ($level >= ($maxLevel - 1)) return $activeChildren;
        }
        $childs = $parent->getChildrenCategories();
        if (count($childs)) {
            foreach ($childs as $child) {
                if ($child->getIsActive()) {
                    array_push($activeChildren, $child);
                }
            }
        }
        return $activeChildren;
    }
    private function explodeByColumns($target, $num) {
        $countChildren = 0;
        foreach ($target as $cat => $childCat) {
            $activeChildCat = $this->getActiveChildren($childCat, 0);
            if ($activeChildCat) {
                $countChildren++;
            }
        }

        $count = count($target);
        if ($count) $target = $this->partition_element($target, $num);
        return $target;
    }
    public function isCategoryActive($category) {
        if ($this->getCurrentCategory()) {
            return in_array($category->getId(), $this->getCurrentCategory()->getPathIds());
        }
        return false;
    }
    public function getStaticBlockFromIdentify($condition = null) {
        $storeId = $this->_storeManager->getStore()->getId();
        $blocks = $this->_blockFactory->create()->setStoreId($storeId)->getCollection()->addFieldToFilter('identifier', array('like' => 'pt_item_menu' . '%'))->addFieldToFilter('is_active', 1);
        return $blocks;
    }
    public function drawCustomMenuBlock($blockId, $is_device = 'mobile') {
        $storeId = $this->_storeManager->getStore()->getId();
        $html = array();
        // --- Static Block ---
        //$blockId = sprintf('pt_custommenu_%d', $id); // --- static block key
        $block = $this->_blockFactory->create()->setStoreId($storeId)->load($blockId);
        //$title = $block->getTitle();
        $id = '_' . $blockId;
        //echo $isActive = $block->getIsActive();die();
        $blockHtml = $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId($blockId)->toHtml();
        $drawPopup = $blockHtml;
        if ($drawPopup) {
            $html[] = '<div id="pt_menu' . $id . '" class="pt_menu">';
        } else {
            $html[] = '<div id="pt_menu' . $id . '" class="pt_menu">';
        }
        // --- Top Menu Item ---
        $html[] = '<div class="parentMenu">';
        $name = $block->getTitle();
        $html[] = '<span class="block-title">' . $name . '</span>';
        $html[] = '</div>';
        // --- Add Popup block (hidden) ---
        if ($drawPopup) {
            // --- Popup function for hide ---
            $html[] = '<div id="popup' . $id . '" class="popup cmsblock" style="display: none; width: 904px;">';
            if ($blockHtml) {
                $html[] = '<div class="block2" id="block2' . $id . '">';
                $html[] = $blockHtml;
                $html[] = '</div>';
            }
            $html[] = '</div>';
        }
        $html[] = '</div>';
        $html = implode("\n", $html);
        if ($is_device == 'mobile') {
            $html = '';
            $html = '<li><a href="#"><span class="name">' . $name . '</span></a>';
            if ($drawPopup) {
                // --- Popup function for hide ---
                $html.= '<div id="popup_mobile' . $id . '" class="popup cmsblock_mobile" style="display: none; width: 904px;">';
                if ($blockHtml) {
                    $html.= '<div class="block2" id="block2' . $id . '">';
                    $html.= $blockHtml;
                    $html.= '</div>';
                }
                $html.= '</div>';
            }
            $html.= '</li>';
        }
        return $html;
    }
    function getThumbUrl($thumb = null) {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/category/' . $thumb;
    }
    public function drawCustomMenuItem($category = null, $level = 0, $last = false, $item = null) {
        if (!$category) return '';
        $category = $this->_categoryInstance->create()->load($category);
        $html = array();
        $blockHtml = '';
        $id = $category->getId();
        // --- Static Block ---
        $blockId = sprintf('pt_menu_idcat_%d', $id); // --- static block key
        $blockHtml = $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId($blockId)->toHtml();;
        /*check block right*/
        $blockIdRight = sprintf('pt_menu_idcat_%d_right', $id); // --- static block key
        $blockHtmlRight = $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId($blockIdRight)->toHtml();
        if ($blockHtmlRight) $blockHtml = $blockHtmlRight;
        // --- Sub Categories ---
        $activeChildren = $this->getActiveChildren($category, $level);
        // --- class for active category ---
        $active = ''; //if ($this->isCategoryActive($category)) $active = ' act';
        // --- Popup functions for show ---
        $drawPopup = ($blockHtml || count($activeChildren));
        if ($drawPopup) {
            $html[] = '<div id="pt_menu' . $id . '" class="pt_menu' . $active . ' nav-' . $item . '">';
        } else {
            $html[] = '<div id="pt_menu' . $id . '" class="pt_menu' . $active . ' nav-' . $item . ' pt_menu_no_child">';
        }
        $is_sale = null;
        $is_new = null;
        if ($category->getIsSale() == 1) {
            $is_sale = '<span class="is_sale">' . $this->getConfig('is_sale') . '</span>';
        }
        if ($category->getIsNew() == 1) {
            $is_new = '<span class="is_new">' . $this->getConfig('is_new') . '</span>';
        }
        $thumb_nail = $this->getThumbUrl($category->getThumbNail());
        $bg_img = $this->getConfig('image');
        $bg_category = json_decode($bg_img);
        // --- Top Menu Item ---
        $link = $this->_catalogCategory->getCategoryUrl($category);
        $is_active = $this->_catalogLayer->get()->getCurrentCategory()->getId();
        if ($is_active == $id) {
            $is_active = 'act';
        } else {
            $is_active = null;
        }
        $arr_catsid = array();
        $is_link = $this->getConfig('is_link');
        $arr_catsid = ['']; //json_decode($is_link);
        $html[] = '<div class="parentMenu" style="">';
        if (in_array($id, $arr_catsid)) {
            $html[] = '<a href="#" class="pt_cate ' . $is_active . '">';
        } else {
            $html[] = '<a href="' . $link . '" class="pt_cate ' . $is_active . '">';
        }
        $name = $category->getName();
        $html[] = '<span>' . $name . '</span>';
        $html[] = $is_sale;
        $html[] = $is_new;
        if (file_exists($thumb_nail)) {
            $html[] = '<img width="50" height="50" src="' . $thumb_nail . '" alt="Thumbnail" />';
        }
        $html[] = '</a>';
        $html[] = '</div>';
        // --- Add Popup block (hidden) ---
        if ($drawPopup == 100) {
            // --- Popup function for hide ---
            $html[] = '<div id="popup' . $id . '"  class="popup" style="display: none; width: 1228px;">';
            // --- draw Sub Categories ---
            if (count($activeChildren)) {
                for ($x = 0; $x <= 36; $x++) {
                    $bId = floor($x/6 +1);
                    $html[] = '<div class="col-md-4 block'.$bId.'" id="block'.$bId.'' . $id . '">';
                    $html[] = $this->drawColumns($activeChildren, $id);
                        if ($blockHtml && $blockHtmlRight) {
                            $html[] = '<div class="column blockright last">';
                            $html[] = $blockHtml;
                            $html[] = '</div>';
                        }
                    $html[] = '<div class="clearBoth"></div>';
                    $html[] = '</div>';
                }
            }
            // --- draw Custom User Block ---
            if ($blockHtml && !$blockHtmlRight) {
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
    public function drawColumns($children, $id) {
        $html = '';
        // --- explode by columns ---
        $columns = $this->getConfig('is_column');
        if ($columns < 1) $columns = 1;
        $chunks = $this->explodeByColumns($children, $columns);
        $columChunk = count($chunks);
        // --- draw columns ---
        $classSpecial = '';
        $keyLast = 0;
        foreach ($chunks as $key => $value) {
            if (count($value)) $keyLast++;
        }
        $blockHtml = '';
        $blockId = sprintf('pt_menu_idcat_%d', $id); // --- static block key
        $blockHtml = $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId($blockId)->toHtml();
        /*Check blog right*/
        $blockIdRight = sprintf('pt_menu_idcat_%d_right', $id); // --- static block key
        $blockHtmlRight = $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId($blockIdRight)->toHtml();
        if ($blockHtmlRight) $blockHtml = $blockHtmlRight;
        foreach ($chunks as $key => $value) {
            if (!count($value)) continue;
            if ($key == $keyLast - 1) {
                $classSpecial = ($blockHtmlRight && $blockHtml) ? '' : ' last';
            } elseif ($key == 0) {
                $classSpecial = ' first';
            } else {
                $classSpecial = '';
            }
            $html.= '<div class="column' . $classSpecial . ' col' . ($key + 1) . '">';
            $html.= $this->drawMenuItem($value, 1, $columChunk);
            $html.= '</div>';
        }
        return $html;
    }
    public function drawMenuItem($children, $level = 1, $columChunk = NULL) {
        $html = '<div class="itemMenu level' . $level . '">';
        $keyCurrent = $this->_catalogLayer->get()->getCurrentCategory()->getId();
        $countChildren = 0;
        $ClassNoChildren = '';
        foreach ($children as $child) {
            $activeChildCat = $this->getActiveChildren($child, 0);
            if ($activeChildCat) {
                $countChildren++;
            }
        }
        if ($countChildren == 0 && $columChunk == 1) {
            $ClassNoChildren = ' nochild';
        }
        $arr_catsid = array();
        $is_link = $this->getConfig('is_link');
        $arr_catsid = ['']; //json_decode($is_link);
        //$arr_catsid = explode(',',$this->getConfig('is_link'));
        foreach ($children as $child) {
            // echo "<pre>"; print_r($child->getData()); echo "</pre>";
            if ($child->getIsActive()) {
                // --- class for active category ---
                $active = '';
                if ($child->getId() == $keyCurrent) $active = ' act';
                // --- format category name ---
                $name = $child->getName();
                $child1 = $this->_categoryInstance->create()->load($child->getId());
                $is_sale = $is_new = null;
                $imageUrl = $child1->getThumbNail();
                if (!$imageUrl) {
                    $imagePlaceholder = $this->helperImageFactory->create();
                    $imageUrl = $this->assetRepos->getUrl($imagePlaceholder->getPlaceholder('thumbnail'));
                }
                if ($child1->getIsSale() == 1) {
                    $is_sale = '<span class="is_sale">' . $this->getConfig('is_sale') . '</span>';
                }
                if ($child1->getIsNew() == 1) {
                    $is_new = '<span class="is_new">' . $this->getConfig('is_new') . '</span>';
                }
                $sub_link = $this->_catalogCategory->getCategoryUrl($child);
                $html.= '<div class="category-img" style="float: left;"><img src="' . $imageUrl . '" alt="Category"  width="24px" height="24px"> </div>';
                if (in_array($child->getId(), $arr_catsid)) {
                    $html.= '<h4 class="itemMenuName level' . $level . $active . $ClassNoChildren . '"><span>' . $name . '</span>' . $is_sale . $is_new . '</h4>';
                } else {
                    $html.= '<a class="itemMenuName level' . $level . $active . $ClassNoChildren . '" href="' . $sub_link . '"><span>' . $name . '</span>' . $is_sale . $is_new . '</a>';
                }
                $activeChildren = $this->getActiveChildren($child, $level);
                if (count($activeChildren) > 0) {
                    $html.= '<div class="itemSubMenu level' . $level . '">';
                    $html.= $this->drawMenuItem($activeChildren, $level + 1, $columChunk);
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
        foreach ($itemsActive as $itemActive) {
            $item++;
            $item1 = explode('_', $itemActive);
            $label = $item1[0];
            $id = $item1[1];
            switch ($label) {
                case "cms":
                    $html.= $this->CmsLink($id, 'mobile');
                break;
                case "block":
                    $html.= $this->CustomLink($id, 'mobile');
                break;
                case "category":
                    $html.= $this->categoryMobile($id);
                break;
            }
        }
        return $html;
    }
    public function categoryMobile($id = null) {
        if (!$id) return;
        $html = null;
        $category = $this->_categoryInstance->create()->load($id);
        $link = $this->_catalogCategory->getCategoryUrl($category);
        $html.= '<li class="level1">';
        $html.= '<a href= "' . $link . '"><span class="name">' . $category->getName() . '</span></a>';
        $html.= '<ul class="level2">';
        $childCate = $category->getChildrenCategories();
        foreach ($childCate as $cate2) {
            $nameLevel2 = $this->_categoryInstance->create()->load($cate2->getId())->getName();
            $html.= '<li>';
            $link2 = $this->_catalogCategory->getCategoryUrl($cate2);
            $html.= '<a href= "' . $link2 . '"><span class="name">' . $nameLevel2 . '</span></a>';
            $cateChild = $cate2->getChildrenCategories();
            $html.= '<ul class="level3">';
            foreach ($cateChild as $cate3) {
                $nameLevel3 = $this->_categoryInstance->create()->load($cate3->getId())->getName();
                $link3 = $this->_catalogCategory->getCategoryUrl($cate3);
                $html.= '<li>';
                $html.= '<a href= "' . $link3 . '"><span class="name">' . $nameLevel3 . '</span></a>';
                $html.= "</li>";
            }
            $html.= "</ul>";
            $html.= "</li>";
        }
        $html.= "</ul>";
        $html.= '</li>';
        return $html;
    }
}
