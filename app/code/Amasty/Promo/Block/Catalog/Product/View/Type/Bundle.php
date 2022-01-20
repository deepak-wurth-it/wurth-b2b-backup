<?php

namespace Amasty\Promo\Block\Catalog\Product\View\Type;

use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View\AbstractView;
use Magento\Catalog\Helper\Product as CatalogProduct;
use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\ResourceModel\Product\CollectionProcessor;
use Magento\Framework\DataObject;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Stdlib\ArrayUtils;

class Bundle extends AbstractView
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var CatalogProduct
     */
    private $catalogProduct;

    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var FormatInterface
     */
    private $localeFormat;

    /**
     * @var array
     */
    private $selectedOptions = [];

    /**
     * @var CollectionProcessor
     */
    private $catalogRuleProcessor;

    /**
     * @var array
     */
    private $optionsPosition = [];

    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        CatalogProduct $catalogProduct,
        EncoderInterface $jsonEncoder,
        FormatInterface $localeFormat,
        CollectionProcessor $catalogRuleProcessor,
        array $data = []
    ) {
        $this->catalogProduct = $catalogProduct;
        $this->jsonEncoder = $jsonEncoder;
        $this->localeFormat = $localeFormat;
        $this->catalogRuleProcessor = $catalogRuleProcessor;
        parent::__construct(
            $context,
            $arrayUtils,
            $data
        );
    }

    /**
     * Returns the bundle product options
     *
     * @param bool $stripSelection
     * @return array
     */
    public function getOptions($stripSelection = false)
    {
        $product = $this->getProduct();
        /** @var Type $typeInstance */
        $typeInstance = $product->getTypeInstance();
        $typeInstance->setStoreFilter($product->getStoreId(), $product);

        $optionCollection = $typeInstance->getOptionsCollection($product);
        $selectionCollection = $typeInstance->getSelectionsCollection(
            $typeInstance->getOptionsIds($product),
            $product
        );
        $this->catalogRuleProcessor->addPriceData($selectionCollection);
        $selectionCollection->addTierPriceData();

        $this->options = $optionCollection->appendSelections(
            $selectionCollection,
            $stripSelection,
            $this->catalogProduct->getSkipSaleableCheck()
        );

        return $this->options;
    }

    /**
     * Return true if product has options
     *
     * @return bool
     */
    public function hasOptions()
    {
        $this->getOptions();
        if (empty($this->options) || !$this->getProduct()->isSalable()) {
            return false;
        }

        return true;
    }

    /**
     * Returns JSON encoded config to be used in JS scripts
     *
     * @return string
     */
    public function getJsonConfig()
    {
        /** @var Option[] $optionsArray */
        $optionsArray = $this->getOptions();
        $options = [];
        $currentProduct = $this->getProduct();

        $defaultValues = [];
        $preConfiguredFlag = $currentProduct->hasPreconfiguredValues();
        /** @var DataObject|null $preConfiguredValues */
        $preConfiguredValues = $preConfiguredFlag ? $currentProduct->getPreconfiguredValues() : null;

        $position = 0;
        foreach ($optionsArray as $optionItem) {
            if (!$optionItem->getSelections()) {
                continue;
            }

            $optionId = $optionItem->getId();
            $options[$optionId] = $this->getOptionItemData($optionItem, $currentProduct, $position);
            $this->optionsPosition[$position] = $optionId;

            if ($preConfiguredFlag) {
                $configValue = $preConfiguredValues->getData('bundle_option/' . $optionId);
                if ($configValue) {
                    $defaultValues[$optionId] = $configValue;
                    $configQty = $preConfiguredValues->getData('bundle_option_qty/' . $optionId);
                    if ($configQty) {
                        $options[$optionId]['selections'][$configValue]['qty'] = $configQty;
                    }
                }

                $options = $this->processOptions($optionId, $options, $preConfiguredValues);
            }

            $position++;
        }

        $config = $this->getConfigData($currentProduct, $options);

        $configObj = new DataObject(
            [
                'config' => $config,
            ]
        );

        $config = $configObj->getConfig();

        if ($preConfiguredFlag && !empty($defaultValues)) {
            $config['defaultValues'] = $defaultValues;
        }

        return $this->jsonEncoder->encode($config);
    }

    /**
     * Get html for option
     *
     * @param Option $option
     * @param Product $product
     * @return string
     */
    public function getOptionHtml(Option $option, Product $product)
    {
        $optionBlock = $this->getChildBlock($option->getType());
        if (!$optionBlock) {
            return $this->escapeHtml(__('There is no defined renderer for "%1" option type.', $option->getType()));
        }

        return $optionBlock->setProduct($product)->setOption($option)->toHtml();
    }

    /**
     * Get formed data from option selection item.
     *
     * @param Product $product
     * @param Product $selection
     *
     * @return array
     */
    private function getSelectionItemData(Product $product, Product $selection)
    {
        $qty = ($selection->getSelectionQty() * 1) ?: '1';
        $selection = [
            'qty' => $qty,
            'customQty' => $selection->getSelectionCanChangeQty(),
            'optionId' => $selection->getId(),
            'prices' => [
                'oldPrice' => [
                    'amount' => 0
                ],
                'basePrice' => [
                    'amount' => 0
                ],
                'finalPrice' => [
                    'amount' => 0
                ],
            ],
            'priceType' => $selection->getSelectionPriceType(),
            'tierPrice' => null,
            'name' => $selection->getName(),
            'canApplyMsrp' => false,
        ];

        return $selection;
    }

    /**
     * Get formed data from selections of option
     *
     * @param Option $option
     * @param Product $product
     * @return array
     */
    private function getSelections(Option $option, Product $product)
    {
        $selections = [];
        $selectionCount = count($option->getSelections());
        foreach ($option->getSelections() as $selectionItem) {
            /* @var $selectionItem Product */
            $selectionId = $selectionItem->getSelectionId();
            $selections[$selectionId] = $this->getSelectionItemData($product, $selectionItem);

            if (($selectionItem->getIsDefault() || $selectionCount == 1 && $option->getRequired())
                && $selectionItem->isSalable()
            ) {
                $this->selectedOptions[$option->getId()][] = $selectionId;
            }
        }

        return $selections;
    }

    /**
     * Get formed data from option
     *
     * @param Option $option
     * @param Product $product
     * @param int $position
     * @return array
     */
    private function getOptionItemData(Option $option, Product $product, $position)
    {
        return [
            'selections' => $this->getSelections($option, $product),
            'title' => $option->getTitle(),
            'isMulti' => in_array($option->getType(), ['multi', 'checkbox']),
            'position' => $position
        ];
    }

    /**
     * Get formed config data from calculated options data
     *
     * @param Product $product
     * @param array $options
     * @return array
     */
    private function getConfigData(Product $product, array $options)
    {
        $isFixedPrice = $this->getProduct()->getPriceType() == Price::PRICE_TYPE_FIXED;
        $config = [
            'options' => $options,
            'selected' => $this->selectedOptions,
            'positions' => $this->optionsPosition,
            'bundleId' => $product->getId(),
            'priceFormat' => $this->localeFormat->getPriceFormat(),
            'prices' => [
                'oldPrice' => [
                    'amount' => 0
                ],
                'basePrice' => [
                    'amount' => 0
                ],
                'finalPrice' => [
                    'amount' => 0
                ]
            ],
            'priceType' => $product->getPriceType(),
            'isFixedPrice' => $isFixedPrice,
        ];

        return $config;
    }

    /**
     * Set preconfigured quantities and selections to options.
     *
     * @param string $optionId
     * @param array $options
     * @param DataObject $preConfiguredValues
     * @return array
     */
    private function processOptions(string $optionId, array $options, DataObject $preConfiguredValues)
    {
        $preConfiguredQtys = $preConfiguredValues->getData("bundle_option_qty/${optionId}") ?? [];
        $selections = $options[$optionId]['selections'];
        array_walk(
            $selections,
            function (&$selection, $selectionId) use ($preConfiguredQtys) {
                if (is_array($preConfiguredQtys) && isset($preConfiguredQtys[$selectionId])) {
                    $selection['qty'] = $preConfiguredQtys[$selectionId];
                } else {
                    if ((int)$preConfiguredQtys > 0) {
                        $selection['qty'] = $preConfiguredQtys;
                    }
                }
            }
        );
        $options[$optionId]['selections'] = $selections;

        return $options;
    }
}
