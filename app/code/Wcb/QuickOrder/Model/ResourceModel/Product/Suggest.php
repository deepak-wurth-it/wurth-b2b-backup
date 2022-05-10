<?php

namespace Wcb\QuickOrder\Model\ResourceModel\Product;

use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierInterfaceFactory;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Helper;
use Magento\QuickOrder\Model\CatalogPermissions\Permissions;
use Magento\Catalog\Api\Data\ProductInterface;

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

    public function __construct(
        Permissions $permissions,
        Helper $dbHelper,
        Visibility $catalogProductVisibility = null,
        SearchResultApplierInterfaceFactory $searchResultApplierInterfaceFactory = null
    ) {
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

        $applier = $this->searchResultApplierInterfaceFactory->create(
            [
                'collection' => $productCollection,
                'searchResult' => $fulltextSearchResults,
                'size' => $fulltextSearchResults->getSearchCriteria()->getPageSize(),
                'currentPage' => $fulltextSearchResults->getSearchCriteria()->getCurrentPage()
            ]
        );
        $applier->apply();
        $this->permissions->applyPermissionsToProductCollection($productCollection);
        $productCollection->setPageSize($resultLimit);

        $query = $this->dbHelper->escapeLikeValue($query, ['position' => 'any']);
        $productCollection->addAttributeToFilter([
            ['attribute' => ProductInterface::SKU, 'like' => $query],
            ['attribute' => ProductInterface::NAME, 'like' => $query],
        ]);

        // here we exclude from collection hidden in catalog products with required custom options.
        $productCollection->setVisibility($this->catalogProductVisibility->getVisibleInSearchIds());

        return $productCollection;
    }
}
