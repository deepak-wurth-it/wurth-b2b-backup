<?php

namespace Wcb\QuickOrder\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierInterfaceFactory;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Helper;
use Magento\QuickOrder\Model\CatalogPermissions\Permissions;
use Wcb\QuickOrder\Helper\Data as QuickOrderHelper;

class Suggest extends \Magento\QuickOrder\Model\ResourceModel\Product\Suggest
{
    /**
     * @var Permissions
     */
    private $permissions;

    /**
     * @var Helper
     */
    private $dbHelper;

    /**
     * Catalog product visibility
     *
     * @var Visibility
     */
    private $catalogProductVisibility;

    /**
     * @var SearchResultApplierInterfaceFactory
     */
    private $searchResultApplierInterfaceFactory;
    /**
     * @var QuickOrderHelper
     */
    protected $quickOrderHelper;

    public function __construct(
        Permissions $permissions,
        Helper $dbHelper,
        QuickOrderHelper $quickOrderHelper,
        Visibility $catalogProductVisibility = null,
        SearchResultApplierInterfaceFactory $searchResultApplierInterfaceFactory = null
    ) {
        $this->quickOrderHelper = $quickOrderHelper;
        $this->permissions = $permissions;
        $this->dbHelper = $dbHelper;
        $this->catalogProductVisibility = $catalogProductVisibility
            ?? ObjectManager::getInstance()->get(Visibility::class);
        $this->searchResultApplierInterfaceFactory = $searchResultApplierInterfaceFactory
            ?? ObjectManager::getInstance()->get(SearchResultApplierInterfaceFactory::class);
        parent::__construct(
            $permissions,
            $dbHelper,
            $catalogProductVisibility,
            $searchResultApplierInterfaceFactory
        );
    }

    public function prepareProductCollection(
        Collection $productCollection,
        SearchResultInterface $fulltextSearchResults,
        $resultLimit,
        $query
    ) {
        $productCollection->addAttributeToSelect(ProductInterface::NAME);
        $productCollection->addAttributeToSelect('product_code');
        //Commented code due to the result not getting.

        /* $applier = $this->searchResultApplierInterfaceFactory->create(
             [
                 'collection' => $productCollection,
                 'searchResult' => $fulltextSearchResults,
                 'size' => $fulltextSearchResults->getSearchCriteria()->getPageSize(),
                 'currentPage' => $fulltextSearchResults->getSearchCriteria()->getCurrentPage()
             ]
         );
         $applier->apply();*/
        $this->permissions->applyPermissionsToProductCollection($productCollection);
        $productCollection->setPageSize($resultLimit);

        // Set custom filter
        $productIds = $this->quickOrderHelper->getProductCodeWithProductId($query);

        $productCollection->addFieldToFilter('entity_id', ['in' => $productIds]);

        // Commented because not need this filter

        /*$query = $this->dbHelper->escapeLikeValue($query, ['position' => 'any']);
        $productCollection->addAttributeToFilter([
            ['attribute' => ProductInterface::SKU, 'like' => $query],
            ['attribute' => ProductInterface::NAME, 'like' => $query],
        ]);*/
        // here we exclude from collection hidden in catalog products with required custom options.
        $productCollection->setVisibility($this->catalogProductVisibility->getVisibleInSearchIds());

        return $productCollection;
    }
}
