<?php
namespace Wcb\QuickOrder\Model\Product\Suggest;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\QuickOrder\Model\FulltextSearch;
use Magento\QuickOrder\Model\ResourceModel\Product\Suggest;

/**
 * Provides suggestions for a user during search phrase typing.
 */
class DataProvider extends \Magento\QuickOrder\Model\Product\Suggest\DataProvider
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var FulltextSearch
     */
    private $fulltextSearch;

    /**
     * @var Suggest
     */
    private $suggestResource;

    /**
     * @var int
     */
    private $resultLimit;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    public function __construct(
        CollectionFactory $collectionFactory,
        FulltextSearch $fulltextSearch,
        Suggest $suggestResource,
        ProductRepositoryInterface $productRepository,
        $resultLimit = 10
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->fulltextSearch = $fulltextSearch;
        $this->suggestResource = $suggestResource;
        $this->resultLimit = $resultLimit;
        $this->productRepository = $productRepository;
        parent::__construct($collectionFactory, $fulltextSearch, $suggestResource, $resultLimit);
    }

    /**
     * Get search result items for auto-suggest functionality.
     *
     * @param string $query
     * @return array
     * @throws LocalizedException
     */
    public function getItems($query)
    {
        $suggestItems = [];
        $page = 0;

        while (count($suggestItems) < $this->resultLimit) {
            $searchResultInterface = $this->fulltextSearch->search('111', $page);
            if (!empty($searchResultInterface->getItems())) {
                /** @var $productCollection Collection */
                $productCollection = $this->collectionFactory->create();
                $productCollection = $this->suggestResource->prepareProductCollection(
                    $productCollection,
                    $searchResultInterface,
                    $this->resultLimit,
                    $query
                );
                $productCollection->load();
                $items = $productCollection->getItems();
                $suggestItems += array_map(function (ProductInterface $item) {
                    //$sku = $item->getSku();
                    $sku = $item->getProductCode();
                    $name = $item->getName();
                    $replacementData = $this->getReplacementData($item);
                    $replacementMsg = isset($replacementData['msg']) ? $replacementData['msg'] : '';
                    $replacementCode = isset($replacementData['replace_code']) ? $replacementData['replace_code'] : $sku;
                    return [
                        'id' => $sku,
                        'labelSku' => $sku,
                        'labelProductName' => $name,
                        'value' => $replacementCode,
                        'replacementProductMsg' => $replacementMsg
                    ];
                }, $items);
                $page++;
            } else {
                break;
            }
        }
        $suggestItems = array_values($suggestItems);

        $suggestItems = $this->sortItems($suggestItems, ['labelProductName', 'id'], $query);
        $suggestItems = $this->orderItemsByExactMatch($suggestItems, ['labelProductName', 'id'], $query);

        return $suggestItems;
    }

    /**
     * Sort suggested items in such way that items which starts with search query will be displayed first.
     *
     * @param array $suggestItems
     * @param array $fieldsToSort
     * @param string $query
     * @return array
     */
    private function sortItems(array $suggestItems, array $fieldsToSort, $query)
    {
        foreach ($fieldsToSort as $fieldToSort) {
            foreach ($suggestItems as $key => $suggestItem) {
                if (stripos(strtolower($suggestItem[$fieldToSort]), $query) === 0) {
                    unset($suggestItems[$key]);
                    array_unshift($suggestItems, $suggestItem);
                }
            }
        }

        return $suggestItems;
    }

    /**
     * Order suggested items by exact match.
     * In some situations fulltext search may provide results with equal relevancy value.
     * Here we move item to the beginning of the results if its field value exactly equal to search query.
     *
     * @param array $suggestItems
     * @param array $fieldsToSort
     * @param string $query
     * @return array
     */
    private function orderItemsByExactMatch(array $suggestItems, array $fieldsToSort, $query)
    {
        foreach ($fieldsToSort as $fieldToSort) {
            foreach ($suggestItems as $key => $suggestItem) {
                if ($suggestItem[$fieldToSort] == $query) {
                    unset($suggestItems[$key]);
                    array_unshift($suggestItems, $suggestItem);
                }
            }
        }

        return $suggestItems;
    }
    public function getReplacementData($product)
    {
        $product = $this->productRepository->getById($product->getId());
        $wcbProductStatus = $product->getWcbProductStatus();
        $replaceProductCode = $product->getSuccessorProductCode();
        $returnData = [];
        $returnData['msg'] = '';
        if ($wcbProductStatus == 3) {
            if ($replaceProductCode) {
                $returnMsg = __("This is replacement product for this " . $replaceProductCode);
                $returnData['replace_code'] = $replaceProductCode;
            } else {
                $returnMsg = __("You are not allowed to add this product.");
            }
            $returnData['msg'] = $returnMsg;

        }
        return $returnData;
    }
}
