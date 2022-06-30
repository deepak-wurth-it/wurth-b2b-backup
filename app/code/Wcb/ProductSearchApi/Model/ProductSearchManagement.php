<?php

namespace Wcb\ProductSearchApi\Model;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductExtension;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory;
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
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Exception as AttributeException;
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
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Adapter\ConnectionException;
use Magento\Framework\DB\Adapter\DeadlockException;
use Magento\Framework\DB\Adapter\LockWaitException;
use Magento\Framework\EntityManager\Operation\Read\ReadExtensions;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\TemporaryState\CouldNotSaveException as TemporaryCouldNotSaveException;
use Magento\Framework\Exception\ValidatorException;
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
        ProductSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Catalog\Model\ResourceModel\Product $resourceModel,
        ProductLinks $linkInitializer,
        LinkTypeProvider $linkTypeProvider,
        StoreManagerInterface $storeManager,
        FilterBuilder $filterBuilder,
        ProductAttributeRepositoryInterface $metadataServiceInterface,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        Converter $optionConverter,
        Filesystem $fileSystem,
        ImageContentValidatorInterface $contentValidator,
        ImageContentInterfaceFactory $contentFactory,
        MimeTypeExtensionMap $mimeTypeExtensionMap,
        ImageProcessorInterface $imageProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        CollectionProcessorInterface $collectionProcessor = null,
        Json $serializer = null,
        $cacheLimit = 1000,
        ReadExtensions $readExtensions = null,
        CategoryLinkManagementInterface $linkManagement = null,
        SearchCriteriaInterface $searchCriteriaInterface,
        FilterGroupBuilder $filterGroupBuilder
    ) {
        $this->productFactory = $productFactory;
        $this->collectionFactory = $collectionFactory;
        $this->initializationHelper = $initializationHelper;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resourceModel = $resourceModel;
        $this->linkInitializer = $linkInitializer;
        $this->linkTypeProvider = $linkTypeProvider;
        $this->storeManager = $storeManager;
        $this->attributeRepository = $attributeRepository;
        $this->filterBuilder = $filterBuilder;
        $this->metadataService = $metadataServiceInterface;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->fileSystem = $fileSystem;
        $this->contentFactory = $contentFactory;
        $this->imageProcessor = $imageProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(Json::class);
        $this->cacheLimit = (int)$cacheLimit;
        $this->readExtensions = $readExtensions ?: ObjectManager::getInstance()
            ->get(ReadExtensions::class);
        $this->linkManagement = $linkManagement ?: ObjectManager::getInstance()
            ->get(CategoryLinkManagementInterface::class);
        $this->searchCriteriaInterface = $searchCriteriaInterface;
        $this->filterGroupBuilder = $filterGroupBuilder;
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
     * @inheritdoc
     */
    /* public function get($sku, $editMode = false, $storeId = null, $forceReload = false)
     {
         $cacheKey = $this->getCacheKey([$editMode, $storeId]);
         $cachedProduct = $this->getProductFromLocalCache($sku, $cacheKey);
         if ($cachedProduct === null || $forceReload) {
             $product = $this->productFactory->create();

             $productId = $this->resourceModel->getIdBySku($sku);
             if (!$productId) {
                 throw new NoSuchEntityException(
                     __("The product that was requested doesn't exist. Verify the product and try again.")
                 );
             }
             if ($editMode) {
                 $product->setData('_edit_mode', true);
             }
             if ($storeId !== null) {
                 $product->setData('store_id', $storeId);
             }
             $product->load($productId);
             $this->cacheProduct($cacheKey, $product);
             $cachedProduct = $product;
         }

         return $cachedProduct;
     }*/

    /**
     * @inheritdoc
     */
    /*  public function getById($productId, $editMode = false, $storeId = null, $forceReload = false)
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
      }*/

    /**
     * Return ProductSearchApi items.
     *
     * @param int $customerId
     * @param string $search
     * @return array
     */
    public function getProductList($customerId, $search)
    {
        $result = [];
        $currentPage = 2;
        $pageSize = 30;
        //$data['customer_id'] = $customerId;

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
//        $filter4 = $this->filterBuilder
//            ->setField('category_id')
//            ->setValue('')
//            ->setConditionType('nin')
//            ->create();

//        $filter_group = $this->filterGroupBuilder
//            ->addFilter($filter1)
//            ->addFilter($filter2)
//            ->addFilter($filter3)
//            ->create();
//        $filter_group1 = $this->filterGroupBuilder
//            ->addFilter($filter4)
//            ->create();

//        $sortOrder = $this->sortOrderBuilder
//            ->setField('sort_order')
//            ->setDirection('DESC')
//            ->create();

        $this->searchCriteriaBuilder->addFilters([$filter1, $filter2,$filter3]);
        //$this->searchCriteriaBuilder->addFilters([$filter1, $filter2,$filter4]);
        // $this->searchCriteriaBuilder->addFilters([$filter4]);
        //$this->searchCriteriaBuilder->addSortOrder($sortOrder);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        //$searchCriteria->setFilterGroups([$filter_group, $filter_group1])
//            ->setPageSize($pageSize)
//            ->setCurrentPage($currentPage);

        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();

        $this->extensionAttributesJoinProcessor->process($collection);

        $collection->addAttributeToSelect('*');
        $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
        $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');

        $collection->getSelect()->join(['cats' => 'catalog_category_product'], 'cats.product_id = e.entity_id');
//        $collection->getSelect()->where('cats.category_id!=""');

        //$this->collectionProcessor->process($searchCriteria, $collection);
        $this->collectionProcessor->process($searchCriteria, $collection);

        $collection->load();

        $collection->addCategoryIds();
        $this->addExtensionAttributes($collection);
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        $productItems =[];
        foreach ($collection->getItems() as $product) {
            $productData =[];
            $this->cacheProduct(
                $this->getCacheKey(
                    [
                        false,
                        $product->getStoreId()
                    ]
                ),
                $product
            );
            $wcbProductStatus = $product->getWcbProductStatus();
            if($wcbProductStatus == 2 || $wcbProductStatus == 3 ){
                $replaceProductCode = $product->getSuccessorProductCode();
                $productData['product_id'] = $product->getProductId();
                $productData['sku'] = $product->getSku();
                $productData['name'] = $product->getName();
                $productData['product_code'] = $product->getProductCode();
                $productData['minimum_sales_unit_quantity'] = $product->getMinimumSalesUnitQuantity();
                $productData['sales_unit_of_measure_id'] = $product->getSalesUnitOfMeasureId();
                $productData['thumbnail'] = $product->getThumbnail();
                $productData['wcb_product_status'] = $wcbProductStatus;
                $productData['successor_product_code'] = $replaceProductCode;
            }else{
                $productData['product_id'] = $product->getProductId();
                $productData['sku'] = $product->getSku();
                $productData['name'] = $product->getName();
                $productData['product_code'] = $product->getProductCode();
                $productData['minimum_sales_unit_quantity'] = $product->getMinimumSalesUnitQuantity();
                $productData['sales_unit_of_measure_id'] = $product->getSalesUnitOfMeasureId();
                $productData['thumbnail'] = $product->getThumbnail();
                $productItems[]= $productData;
            }

        }
        $data['search'] = ['search_term'=>$search];
        $data['data'] = array(
                'total_count'=>$collection->getSize(),
                'current_page'=> $currentPage,
                'page_size'=> $pageSize,
                'items'=>$productItems
        );
        $result[]= $data;
        return $result;
    }

    /**
     * Add extension attributes to loaded items.
     *
     * @param Collection $collection
     * @return Collection
     */
    private function addExtensionAttributes(Collection $collection): Collection
    {
        foreach ($collection->getItems() as $item) {
            $this->readExtensions->execute($item);
        }
        return $collection;
    }

    /**
     * Merge data from DB and updates from request
     *
     * @param array $productData
     * @param bool $createNew
     * @return ProductInterface|Product
     * @throws NoSuchEntityException
     */
    /*   protected function initializeProductData(array $productData, $createNew)
       {
           unset($productData['media_gallery']);
           if ($createNew) {
               $product = $this->productFactory->create();
               $this->assignProductToWebsites($product);
           } elseif (!empty($productData['id'])) {
               $this->removeProductFromLocalCacheById($productData['id']);
               $product = $this->getById($productData['id']);
           } else {
               $this->removeProductFromLocalCacheBySku($productData['sku']);
               $product = $this->get($productData['sku']);
           }

           foreach ($productData as $key => $value) {
               $product->setData($key, $value);
           }

           return $product;
       }*/

    /**
     * Assign product to websites.
     *
     * @param Product $product
     * @return void
     */
    /*  private function assignProductToWebsites(\Magento\Catalog\Model\Product $product)
      {
          if ($this->storeManager->getStore(true)->getCode() == \Magento\Store\Model\Store::ADMIN_CODE) {
              $websiteIds = array_keys($this->storeManager->getWebsites());
          } else {
              $websiteIds = [$this->storeManager->getStore()->getWebsiteId()];
          }

          $product->setWebsiteIds($websiteIds);
      }*/

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
     * Process product links, creating new links, updating and deleting existing links
     *
     * @param ProductInterface $product
     * @param ProductLinkInterface[] $newLinks
     * @return $this
     * @throws NoSuchEntityException
     */
    /*   private function processLinks(ProductInterface $product, $newLinks)
       {
           if ($newLinks === null) {
               // If product links were not specified, don't do anything
               return $this;
           }

           // Clear all existing product links and then set the ones we want
           $linkTypes = $this->linkTypeProvider->getLinkTypes();
           foreach (array_keys($linkTypes) as $typeName) {
               $this->linkInitializer->initializeLinks($product, [$typeName => []]);
           }

           // Set each linktype info
           if (!empty($newLinks)) {
               $productLinks = [];
               foreach ($newLinks as $link) {
                   $productLinks[$link->getLinkType()][] = $link;
               }

               foreach ($productLinks as $type => $linksByType) {
                   $assignedSkuList = [];
                   /** @var \Magento\Catalog\Api\Data\ProductLinkInterface $link */
//                foreach ($linksByType as $link) {
//                    $assignedSkuList[] = $link->getLinkedProductSku();
//                }
//                $linkedProductIds = $this->resourceModel->getProductsIdsBySkus($assignedSkuList);
//
//                $linksToInitialize = [];
//                foreach ($linksByType as $link) {
//                    $linkDataArray = $this->extensibleDataObjectConverter
//                        ->toNestedArray($link, [], \Magento\Catalog\Api\Data\ProductLinkInterface::class);
//                    $linkedSku = $link->getLinkedProductSku();
//                    if (!isset($linkedProductIds[$linkedSku])) {
//                        throw new NoSuchEntityException(
//                            __('The Product with the "%1" SKU doesn\'t exist.', $linkedSku)
//                        );
//                    }
//                    $linkDataArray['product_id'] = $linkedProductIds[$linkedSku];
//                    $linksToInitialize[$linkedProductIds[$linkedSku]] = $linkDataArray;
//                }
//
//                $this->linkInitializer->initializeLinks($product, [$type => $linksToInitialize]);
//            }
//        }
//
//        $product->setProductLinks($newLinks);
//        return $this;
//    }*/

    /**
     * Process Media gallery data before save product.
     *
     * Compare Media Gallery Entries Data with existing Media Gallery
     * * If Media entry has not value_id set it as new
     * * If Existing entry 'value_id' absent in Media Gallery set 'removed' flag
     * * Merge Existing and new media gallery
     *
     * @param ProductInterface $product contains only existing media gallery items
     * @param array $mediaGalleryEntries array which contains all media gallery items
     * @return $this
     * @throws InputException
     * @throws StateException
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    /*   protected function processMediaGallery(ProductInterface $product, $mediaGalleryEntries)
       {
           $this->getMediaGalleryProcessor()->processMediaGallery($product, $mediaGalleryEntries);

           return $this;
       }*/

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    /*  public function save(ProductInterface $product, $saveOptions = false)
      {
          $assignToCategories = false;
          $tierPrices = $product->getData('tier_price');

          try {
              $existingProduct = $product->getId() ? $this->getById($product->getId()) : $this->get($product->getSku());

              $product->setData(
                  $this->resourceModel->getLinkField(),
                  $existingProduct->getData($this->resourceModel->getLinkField())
              );
              if (!$product->hasData(Product::STATUS)) {
                  $product->setStatus($existingProduct->getStatus());
              }

              /** @var ProductExtension $extensionAttributes */
//            $extensionAttributes = $product->getExtensionAttributes();
//            if (empty($extensionAttributes->__toArray())) {
//                $product->setExtensionAttributes($existingProduct->getExtensionAttributes());
//                $assignToCategories = true;
//            }
//        } catch (NoSuchEntityException $e) {
//            $existingProduct = null;
//        }
//
//        $productDataArray = $this->extensibleDataObjectConverter
//            ->toNestedArray($product, [], ProductInterface::class);
//        $productDataArray = array_replace($productDataArray, $product->getData());
//        $ignoreLinksFlag = $product->getData('ignore_links_flag');
//        $productLinks = null;
//        if (!$ignoreLinksFlag && $ignoreLinksFlag !== null) {
//            $productLinks = $product->getProductLinks();
//        }
//        if (!isset($productDataArray['store_id'])) {
//            $productDataArray['store_id'] = (int) $this->storeManager->getStore()->getId();
//        }
//        $product = $this->initializeProductData($productDataArray, empty($existingProduct));
//
//        $this->processLinks($product, $productLinks);
//        if (isset($productDataArray['media_gallery'])) {
//            $this->processMediaGallery($product, $productDataArray['media_gallery']['images']);
//        }
//
//        if (!$product->getOptionsReadonly()) {
//            $product->setCanSaveCustomOptions(true);
//        }
//
//        $validationResult = $this->resourceModel->validate($product);
//        if (true !== $validationResult) {
//            throw new \Magento\Framework\Exception\CouldNotSaveException(
//                __('Invalid product data: %1', implode(',', $validationResult))
//            );
//        }
//
//        if ($tierPrices !== null) {
//            $product->setData('tier_price', $tierPrices);
//        }
//
//        $this->saveProduct($product);
//        if ($assignToCategories === true && $product->getCategoryIds()) {
//            $this->linkManagement->assignProductToCategories(
//                $product->getSku(),
//                $product->getCategoryIds()
//            );
//        }
//        $this->removeProductFromLocalCacheBySku($product->getSku());
//        $this->removeProductFromLocalCacheById($product->getId());
//
//        return $this->get($product->getSku(), false, $product->getStoreId());
//    }*/

    /**
     * @inheritdoc
     */
    /*   public function delete(ProductInterface $product)
       {
           $sku = $product->getSku();
           $productId = $product->getId();
           try {
               $this->removeProductFromLocalCacheBySku($product->getSku());
               $this->removeProductFromLocalCacheById($product->getId());
               $this->resourceModel->delete($product);
           } catch (ValidatorException $e) {
               throw new CouldNotSaveException(__($e->getMessage()), $e);
           } catch (\Exception $e) {
               throw new \Magento\Framework\Exception\StateException(
                   __('The "%1" product couldn\'t be removed.', $sku),
                   $e
               );
           }
           $this->removeProductFromLocalCacheBySku($sku);
           $this->removeProductFromLocalCacheById($productId);

           return true;
       }*/

    /**
     * @inheritdoc
     */
    /*   public function deleteById($sku)
       {
           $product = $this->get($sku);
           return $this->delete($product);
       }*/

    /**
     * @inheritdoc
     */
    /*   public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
       {
           /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
//        $collection = $this->collectionFactory->create();
//        $this->extensionAttributesJoinProcessor->process($collection);
//
//        $collection->addAttributeToSelect('*');
//        $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
//        $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
//
//        $this->collectionProcessor->process($searchCriteria, $collection);
//
//        $collection->load();
//
//        $collection->addCategoryIds();
//        $this->addExtensionAttributes($collection);
//        $searchResult = $this->searchResultsFactory->create();
//        $searchResult->setSearchCriteria($searchCriteria);
//        $searchResult->setItems($collection->getItems());
//        $searchResult->setTotalCount($collection->getSize());
//
//        foreach ($collection->getItems() as $product) {
//            $this->cacheProduct(
//                $this->getCacheKey(
//                    [
//                        false,
//                        $product->getStoreId()
//                    ]
//                ),
//                $product
//            );
//        }
//
//        return $searchResult;
//    }*/

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
     * Clean internal product cache
     *
     * @return void
     */
    /*   public function cleanCache()
       {
           $this->instances = null;
           $this->instancesById = null;
       }*/

    /**
     * Retrieve media gallery processor.
     *
     * @return MediaGalleryProcessor
     */
    /*   private function getMediaGalleryProcessor()
       {
           if (null === $this->mediaProcessor) {
               $this->mediaProcessor = \Magento\Framework\App\ObjectManager::getInstance()
                   ->get(MediaGalleryProcessor::class);
           }

           return $this->mediaProcessor;
       }*/

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
     * Gets product from the local cache by SKU.
     *
     * @param string $sku
     * @param string $cacheKey
     * @return Product|null
     */
    /*   private function getProductFromLocalCache(string $sku, string $cacheKey)
       {
           $preparedSku = $this->prepareSku($sku);

           return $this->instances[$preparedSku][$cacheKey] ?? null;
       }*/

    /**
     * Removes product in the local cache by sku.
     *
     * @param string $sku
     * @return void
     */
    /*   private function removeProductFromLocalCacheBySku(string $sku): void
       {
           $preparedSku = $this->prepareSku($sku);
           unset($this->instances[$preparedSku]);
       }*/

    /**
     * Removes product in the local cache by id.
     *
     * @param string|null $id
     * @return void
     */
    /*   private function removeProductFromLocalCacheById(?string $id): void
       {
           unset($this->instancesById[$id]);
       }*/

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
     * @param Collection $collection
     * @return void
     * @deprecated 102.0.0
     */
    protected function addFilterGroupToCollection(
        FilterGroup $filterGroup,
        Collection $collection
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
     * Save product resource model.
     *
     * @param ProductInterface|Product $product
     * @throws TemporaryCouldNotSaveException
     * @throws InputException
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    /* private function saveProduct($product): void
     {
         try {
             $this->removeProductFromLocalCacheBySku($product->getSku());
             $this->removeProductFromLocalCacheById($product->getId());
             $this->resourceModel->save($product);
         } catch (ConnectionException $exception) {
             throw new TemporaryCouldNotSaveException(
                 __('Database connection error'),
                 $exception,
                 $exception->getCode()
             );
         } catch (DeadlockException $exception) {
             throw new TemporaryCouldNotSaveException(
                 __('Database deadlock found when trying to get lock'),
                 $exception,
                 $exception->getCode()
             );
         } catch (LockWaitException $exception) {
             throw new TemporaryCouldNotSaveException(
                 __('Database lock wait timeout exceeded'),
                 $exception,
                 $exception->getCode()
             );
         } catch (AttributeException $exception) {
             throw InputException::invalidFieldValue(
                 $exception->getAttributeCode(),
                 $product->getData($exception->getAttributeCode()),
                 $exception
             );
         } catch (ValidatorException $e) {
             throw new CouldNotSaveException(__($e->getMessage()));
         } catch (LocalizedException $e) {
             throw $e;
         } catch (\Exception $e) {
             throw new CouldNotSaveException(
                 __('The product was unable to be saved. Please try again.'),
                 $e
             );
         }
     }*/
}
