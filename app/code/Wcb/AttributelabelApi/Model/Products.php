<?php

namespace Wcb\AttributelabelApi\Model;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Wcb\AttributelabelApi\Api\ProductsInterface;
class Products implements ProductsInterface
{
   /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var Product[]
     */
    protected $instances = [];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $resourceModel;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Helper\ImageFactory
     */
    protected $helperFactory;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $appEmulation;

    /**
     * Review model
     *
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;

     /**
     * Review resource model
     *
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    protected $_reviewsColFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    protected $_attributeLoading;

    /**
     * ProductRepository constructor.
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product $resourceModel
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param  \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param  \Magento\Review\Model\ResourceModel\Review\CollectionFactory $collectionFactory
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product $resourceModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Catalog\Helper\ImageFactory $helperFactory,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $collectionFactory,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\ResourceModel\ProductFactory   $attributeLoading
    ) {
        $this->productFactory       =  $productFactory;
        $this->storeManager         =  $storeManager;
        $this->resourceModel        =  $resourceModel;
        $this->helperFactory        =  $helperFactory;
        $this->appEmulation         =  $appEmulation;
        $this->_reviewFactory       =  $reviewFactory;
        $this->_reviewsColFactory   =  $collectionFactory;
        $this->priceCurrency        =  $priceCurrency;
        $this->_attributeLoading    =  $attributeLoading;
    }

    public function getAdditional($sku, $editMode = false, $storeId = null, $forceReload = false)
    {
        $cacheKey = $this->getCacheKey([$editMode, $storeId]);
        if (!isset($this->instances[$sku][$cacheKey]) || $forceReload) {
            $product = $this->productFactory->create();

            $productId = $this->resourceModel->getIdBySku($sku);
            if (!$productId) {

                throw new NoSuchEntityException(__('Requested product doesn\'t exist'));
            }
            if ($editMode) {
                $product->setData('_edit_mode', true);
            }
            if ($storeId !== null) {
                $product->setData('store_id', $storeId);
            } else {

                $storeId = $this->storeManager->getStore()->getId();
            }
            $product->load($productId);

            $excludeAttr = [];
            $attributes = $product->getAttributes();

            foreach ($attributes as $attribute) {
               $data = [];
               $optionId='';
                 if ($this->isVisibleOnFrontend($attribute, $excludeAttr))
                 {
                    $code = $attribute->getAttributeCode();
                    $value = $product->getResource()->getAttributeRawValue($product->getId(), $code, '1');
                    if ($value instanceof Phrase) {
                        $value = (string)$value;
                    } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                        $value = $this->priceCurrency->convertAndFormat($value);
                    } elseif ($attribute->getFrontendInput() == 'select') {
                        $value = $attribute->getSource()->getOptionText($value);

                        $attr = $product->getResource()->getAttribute($code);
                        if ($attr->usesSource()) {
                            $optionId = $attr->getSource()->getOptionId($value);
                        }

                    } elseif ($attribute->getFrontendInput() == 'multiselect') {
                     // added if condition in order or resolve the explode issue if value is empty.
                         if(!empty($value) && $value) {
                            $multiselectOptionsArray = explode(',', $value);
                         foreach ($multiselectOptionsArray as $k => $optionKey) {
                            $multiselectOptionsArray[$k] = $attribute->getSource()->getOptionText($optionKey);
                         }
                        $value = implode(', ', $multiselectOptionsArray);
                        $multiSelectValue = explode(', ', $value);

                            foreach ($multiSelectValue as $a => $attValue) {
                                $attr = $product->getResource()->getAttribute($code);
                                if ($attr->usesSource()) {
                                    $optionIdInfo = $attr->getSource()->getOptionId($attValue);
                                    $attArray[$a] = $optionIdInfo;
                                    $optionId = implode(', ', $attArray);
                                }
                            }
                         }
                     }
                    if (is_string($value) && strlen($value)) {
                        $data[$attribute->getAttributeCode()] = [
                            'label' => $attribute->getFrontendLabel(),
                            'value' => __($value),
                            'options_value' => $optionId,
                            'visible_on_storefront' => $attribute->getIsVisibleOnFront()
                        ];
                    }
                    if(!empty($value)){
                        $product->setCustomAttribute($attribute->getAttributeCode(), $data);
                    }
                 }
             }

            $this->instances[$sku][$cacheKey] = $product;
            $this->instancesById[$product->getId()][$cacheKey] = $product;
        }
        return $this->instancesById[$product->getId()][$cacheKey];
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
        return md5(serialize($serializeData));
    }

    protected function isVisibleOnFrontend(
        \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute,
        array $excludeAttr
    ) {
        return ($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), $excludeAttr));
    }
}