<?php

namespace Wcb\ProductSearchApi\Model;

//use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductInterface;

//use Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\Catalog\Model\Api\SearchCriteria\ProductCollectionProcessor;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Gallery\MimeTypeExtensionMap;
use Magento\Catalog\Model\Product\Gallery\Processor;
use Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks;
use Magento\Catalog\Model\Product\LinkTypeProvider;
use Magento\Catalog\Model\Product\Option\Converter;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ProductRepository\MediaGalleryProcessor;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\Api\Data\ImageContentInterfaceFactory;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\ImageContentValidatorInterface;
use Magento\Framework\Api\ImageProcessorInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\Operation\Read\ReadExtensions;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
use Wcb\ProductSearchApi\Api\ProductSearchManagementInterface;

/**
 * Defines the implementaiton class of the \Wcb\ProductSearchApi\Api\ProductSearchManagementInterface
 */
class ProductSearchManagement implements ProductSearchManagementInterface
{
    /**
     * @var ProductCustomOptionRepositoryInterface
     */
    protected $optionRepository;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var Product[]
     */
    protected $instances = [];

    /**
     * @var Product[]
     */
    protected $instancesById = [];

    /**
     * @var Helper
     */
    protected $initializationHelper;

    /**
     * @var ProductSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $resourceModel;

    /**
     * @var ProductLinks
     */
    protected $linkInitializer;

    /**
     * @var LinkTypeProvider
     */
    protected $linkTypeProvider;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected $metadataService;

    /**
     * @var ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @deprecated 103.0.2
     *
     * @var ImageContentInterfaceFactory
     */
    protected $contentFactory;

    /**
     * @deprecated 103.0.2
     *
     * @var ImageProcessorInterface
     */
    protected $imageProcessor;

    /**
     * @var JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @deprecated 103.0.2
     *
     * @var Processor
     */
    protected $mediaGalleryProcessor;

    /**
     * @var MediaGalleryProcessor
     */
    private $mediaProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var int
     */
    private $cacheLimit = 0;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var ReadExtensions
     */
    private $readExtensions;

    /**
     * @var CategoryLinkManagementInterface
     */
    private $linkManagement;
    /**
     * @var SearchCriteriaInterface
     */
    private $searchCriteriaInterface;
    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;
    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;
    /**
     * @var CustomerCollectionFactory
     */
    private $customerCollectionFactory;
    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * ProductRepository constructor.
     * @param ProductFactory $productFactory
     * @param Helper $initializationHelper
     * @param ProductSearchResultsInterfaceFactory $searchResultsFactory
     * @param ResourceModel\Product\CollectionFactory $collectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param ResourceModel\Product $resourceModel
     * @param ProductLinks $linkInitializer
     * @param LinkTypeProvider $linkTypeProvider
     * @param StoreManagerInterface $storeManager
     * @param FilterBuilder $filterBuilder
     * @param ProductAttributeRepositoryInterface $metadataServiceInterface
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param Converter $optionConverter
     * @param Filesystem $fileSystem
     * @param ImageContentValidatorInterface $contentValidator
     * @param ImageContentInterfaceFactory $contentFactory
     * @param MimeTypeExtensionMap $mimeTypeExtensionMap
     * @param ImageProcessorInterface $imageProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CollectionProcessorInterface $collectionProcessor [optional]
     * @param Json|null $serializer
     * @param int $cacheLimit [optional]
     * @param ReadExtensions $readExtensions
     * @param CategoryLinkManagementInterface $linkManagement
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ProductFactory $productFactory,
        Helper $initializationHelper,
        //ProductSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        //ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Catalog\Model\ResourceModel\Product $resourceModel,
        ProductLinks $linkInitializer,
        LinkTypeProvider $linkTypeProvider,
        StoreManagerInterface $storeManager,
        FilterBuilder $filterBuilder,
        ProductAttributeRepositoryInterface $metadataServiceInterface,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        Converter $optionConverter,
        //Filesystem $fileSystem,
        //ImageContentValidatorInterface $contentValidator,
        //ImageContentInterfaceFactory $contentFactory,
        //MimeTypeExtensionMap $mimeTypeExtensionMap,
        //ImageProcessorInterface $imageProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        CollectionProcessorInterface $collectionProcessor = null,
        Json $serializer = null,
        $cacheLimit = 1000,
        ReadExtensions $readExtensions = null,
        //CategoryLinkManagementInterface $linkManagement = null,
        SearchCriteriaInterface $searchCriteriaInterface,
        FilterGroupBuilder $filterGroupBuilder,
        SortOrderBuilder $sortOrderBuilder,
        CustomerCollectionFactory $customerCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory
    ) {
        $this->productFactory = $productFactory;
        $this->collectionFactory = $collectionFactory;
        $this->initializationHelper = $initializationHelper;
        //$this->searchResultsFactory = $searchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resourceModel = $resourceModel;
        $this->linkInitializer = $linkInitializer;
        $this->linkTypeProvider = $linkTypeProvider;
        $this->storeManager = $storeManager;
        //$this->attributeRepository = $attributeRepository;
        $this->filterBuilder = $filterBuilder;
        $this->metadataService = $metadataServiceInterface;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        //$this->fileSystem = $fileSystem;
        //$this->contentFactory = $contentFactory;
        //$this->imageProcessor = $imageProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(Json::class);
        $this->cacheLimit = (int)$cacheLimit;
        $this->readExtensions = $readExtensions ?: ObjectManager::getInstance()
            ->get(ReadExtensions::class);
        //$this->linkManagement = $linkManagement ?: ObjectManager::getInstance()
        //  ->get(CategoryLinkManagementInterface::class);
        $this->searchCriteriaInterface = $searchCriteriaInterface;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Retrieve collection processor
     *
     * @return CollectionProcessorInterface
     * @deprecated 102.0.0
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = ObjectManager::getInstance()->get(
            // phpstan:ignore "Class Magento\Catalog\Model\Api\SearchCriteria\ProductCollectionProcessor not found."
                ProductCollectionProcessor::class
            );
        }
        return $this->collectionProcessor;
    }

    /**
     * @param string $product_code
     * @return mixed|void
     */
    public function getProductByCode($product_code)
    {
        $result = [];
        $filter1 = $this->filterBuilder
            ->setField('product_code')
            ->setValue($product_code)
            ->setConditionType('eq')
            ->create();
        $this->searchCriteriaBuilder->addFilters([$filter1]);
        $searchCriteria = $this->searchCriteriaBuilder->create();

        /** @var CollectionAlias $collection */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $collection->load();
        $sku = $collection->getFirstItem()->getSku();
        $data['sku'] = $sku;
        $result[] = $data;
        return $result;
    }

    /**
     * Return ProductSearchApi items.
     *
     * @param int $customerId
     * @param string $search
     * @param int $page
     * @return array
     */
    public function getProductList($customerId, $search, $page = 1, $group_id)
    {
        $result = [];
        $pageSize = 10;
        
        $filter1 = $this->filterBuilder
            ->setField('name')
            ->setValue('%' . $search . '%')
            ->setConditionType('like')
            ->create();
        $filter2 = $this->filterBuilder
            ->setField('description')
            ->setValue('%' . $search . '%')
            ->setConditionType('like')
            ->create();
        $filter3 = $this->filterBuilder
            ->setField('product_code')
            ->setValue('%' . $search . '%')
            ->setConditionType('like')
            ->create();

        $sortOrder = $this->sortOrderBuilder
            ->setField('sort_order')
            ->setDirection('DESC')
            ->create();

        $this->searchCriteriaBuilder->addFilters([$filter1, $filter2, $filter3]);
        $this->searchCriteriaBuilder->addSortOrder($sortOrder);
        $searchCriteria = $this->searchCriteriaBuilder->create()
            ->setPageSize($pageSize)
            ->setCurrentPage($page);

        /** @var CollectionAlias $collection */
        $collection = $this->collectionFactory->create();

        $this->extensionAttributesJoinProcessor->process($collection);

        $collection->addAttributeToSelect('*');
        $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
        $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        $collectionClone = clone $collection;
        $collection->getSelect()->join(['cats' => 'catalog_category_product'], 'cats.product_id = e.entity_id');
        /*   if ($group_id) {
               $customerCollection = $this->customerCollectionFactory->create()
                   ->addFieldToFilter('customer_group_id', ['eq' => $group_id]);
               $branch_code = $customerCollection->getFirstItem()->getDataByKey('branch_code');
               if ($branch_code == 'B' || $branch_code == "T") {
                   $catCollection = $this->categoryCollectionFactory->create()
                       ->addFieldToFilter('pim_category_code', ['like' => 'Ex%']);
                      $catAllIds = $catCollection->getAllIds();
                      print_r($catAllIds);
                       // $collection->getSelect()->where('cats.category_id!=""');
                      // ->getItemsByColumnValue('entity_id');
                   echo $catCollection->getSelect();
               } else {
                   echo "No";
               }
           }
           exit;*/
        $this->collectionProcessor->process($searchCriteria, $collection);

        $collection->load();

        // $collection->addCategoryIds();
        //$this->addExtensionAttributes($collection);
        $productItems = [];
        foreach ($collection->getItems() as $product) {
            $productData = [];
            $this->cacheProduct(
                $this->getCacheKey(
                    [
                        false,
                        $product->getStoreId()
                    ]
                ),
                $product
            );
            $product_id = ($product->getProductId() != null) ? $product->getProductId() : $product->getEntityId();
            $productObj = $this->getById($product_id);
            $wcbProductStatus = $productObj->getWcbProductStatus();
            $successor_product_value = $productObj->getSuccessorProductCode();
            $successor_product_code = [];
            if ($wcbProductStatus == 2 || $wcbProductStatus == 3) {
//                if ($search == $product->getProductCode()) {
//                    $successor_product_code[] =   $product->getSuccessorProductCode();
//                }
                if ($successor_product_value) {
                    $successor_product_code[] = $successor_product_value;
                }
            } else {
                $productItems[] = $this->prepareGetProductResponse($productObj);
            }
        }
        if (!empty($successor_product_code)) {
            $productItems = $this->successorProductCollections($collectionClone, $successor_product_code, $productItems);
        }
        $data['search'] = ['search_term' => $search];
        $data['data'] = [
            'total_count' => $collection->getSize(),
            'current_page' => $page,
            'page_size' => $pageSize,
            'items' => $productItems
        ];
        $result[] = $data;
        return $result;
    }

    /**
     * Add product to internal cache and truncate cache if it has more than cacheLimit elements.
     *
     * @param string $cacheKey
     * @param ProductInterface $product
     * @return void
     */
    private function cacheProduct($cacheKey, ProductInterface $product)
    {
        $this->instancesById[$product->getId()][$cacheKey] = $product;
        $this->saveProductInLocalCache($product, $cacheKey);

        if ($this->cacheLimit && count($this->instances) > $this->cacheLimit) {
            $offset = round($this->cacheLimit / -2);
            $this->instancesById = array_slice($this->instancesById, $offset, null, true);
            $this->instances = array_slice($this->instances, $offset, null, true);
        }
    }

    /**
     * Saves product in the local cache by sku.
     *
     * @param Product $product
     * @param string $cacheKey
     * @return void
     */
    private function saveProductInLocalCache(Product $product, string $cacheKey): void
    {
        $preparedSku = $this->prepareSku($product->getSku());
        $this->instances[$preparedSku][$cacheKey] = $product;
    }

    /**
     * Converts SKU to lower case and trims.
     *
     * @param string $sku
     * @return string
     */
    private function prepareSku(string $sku): string
    {
        return mb_strtolower(trim($sku));
    }

    /**
     * Get key for cache
     *
     * @param array $data
     * @return string
     */
    protected function getCacheKey($data)
    {
        $serializeData = [];
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $serializeData[$key] = $value->getId();
            } else {
                $serializeData[$key] = $value;
            }
        }
        $serializeData = $this->serializer->serialize($serializeData);
        return sha1($serializeData);
    }

    /**
     * @inheritdoc
     */
    public function getById($productId, $editMode = false, $storeId = null, $forceReload = false)
    {
        $cacheKey = $this->getCacheKey([$editMode, $storeId]);
        if (!isset($this->instancesById[$productId][$cacheKey]) || $forceReload) {
            $product = $this->productFactory->create();
            if ($editMode) {
                $product->setData('_edit_mode', true);
            }
            if ($storeId !== null) {
                $product->setData('store_id', $storeId);
            }
            $product->load($productId);
            if (!$product->getId()) {
                throw new NoSuchEntityException(
                    __("The product that was requested doesn't exist. Verify the product and try again.")
                );
            }
            $this->cacheProduct($cacheKey, $product);
        }
        return $this->instancesById[$productId][$cacheKey];
    }

    /**
     * @param $product
     */
    public function prepareGetProductResponse($product)
    {
        $productData = [];
        $productData['product_id'] = ($product->getProductId() != null) ? $product->getProductId() : $product->getEntityId();
        $productData['sku'] = $product->getSku();
        $productData['name'] = $product->getName();
        $productData['product_code'] = $product->getProductCode();
        $productData['minimum_sales_unit_quantity'] = $product->getMinimumSalesUnitQuantity();
        $productData['sales_unit_of_measure_id'] = $product->getSalesUnitOfMeasureId();
        $productData['thumbnail'] = $product->getThumbnail();
        return $productData;
    }

    public function successorProductCollections($collectionClone, $successor_product_code, $productItems)
    {
        $filterObject = $this->filterBuilder
            ->setField('product_code')
            ->setValue($successor_product_code)
            ->setConditionType('in')
            ->create();

        $filter_group = $this->filterGroupBuilder
            ->addFilter($filterObject)
            ->create();
        $successorSearchCriteria = $this->searchCriteriaBuilder->create()
            ->setFilterGroups([$filter_group]);

        $this->collectionProcessor->process($successorSearchCriteria, $collectionClone);

        $collectionClone->load();

        //$collectionClone->addCategoryIds();
        //$this->addExtensionAttributes($collectionClone);

        foreach ($collectionClone->getItems() as $product) {
            $productItems[] = $this->prepareGetProductResponse($product);
        }
        return $productItems;
    }

    /**
     * Process new gallery media entry.
     *
     * @param ProductInterface $product
     * @param array $newEntry
     * @return $this
     * @throws InputException
     * @throws StateException
     * @throws LocalizedException
     * @deprecated 103.0.2
     * @see MediaGalleryProcessor::processNewMediaGalleryEntry()
     *
     */
    protected function processNewMediaGalleryEntry(
        ProductInterface $product,
        array $newEntry
    ) {
        $this->getMediaGalleryProcessor()->processNewMediaGalleryEntry($product, $newEntry);
        return $this;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param CollectionAlias $collection
     * @return void
     * @deprecated 102.0.0
     */
    protected function addFilterGroupToCollection(
        FilterGroup $filterGroup,
        CollectionAlias $collection
    ) {
        $fields = [];
        $categoryFilter = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $conditionType = $filter->getConditionType() ?: 'eq';

            if ($filter->getField() == 'category_id') {
                $categoryFilter[$conditionType][] = $filter->getValue();
                continue;
            }
            $fields[] = ['attribute' => $filter->getField(), $conditionType => $filter->getValue()];
        }

        if ($categoryFilter) {
            $collection->addCategoriesFilter($categoryFilter);
        }

        if ($fields) {
            $collection->addFieldToFilter($fields);
        }
    }

    /**
     * Add extension attributes to loaded items.
     *
     * @param CollectionAlias $collection
     * @return CollectionAlias
     */
    private function addExtensionAttributes(CollectionAlias $collection): CollectionAlias
    {
        foreach ($collection->getItems() as $item) {
            $this->readExtensions->execute($item);
        }
        return $collection;
    }
}
