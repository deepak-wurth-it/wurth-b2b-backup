<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-navigation
 * @version   2.0.12
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Brand\Block;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Mirasvit\Brand\Model\Config\Config;
use Mirasvit\Brand\Repository\BrandRepository;

class MoreFromBrand extends AbstractProduct
{
    const DEFAULT_PRODUCT_LIMIT = 6;
    const BRAND_NAME            = '{brand_name}';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $productCollection;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    private   $brandRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var ProductVisibility
     */
    private $productVisibility;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        BrandRepository $brandRepository,
        Context $context,
        Config $config,
        CollectionFactory $productCollectionFactory,
        ProductVisibility $productVisibility,
        ResourceConnection $resourceConnection,
        array $data = []
    ) {
        $this->registry                 = $context->getRegistry();
        $this->brandRepository          = $brandRepository;
        $this->config                   = $config;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productVisibility        = $productVisibility;
        $this->resourceConnection       = $resourceConnection;

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductCollection()
    {
        return $this->productCollection;
    }

    /**
     * {@inheritdoc}
     */
    protected function setProductCollection()
    {
        $product              = $this->registry->registry('product');
        $brandAttributeCode   = $this->getBrandAttribute();
        $brandAttributeOption = $product->getData($brandAttributeCode);
        if ($brandAttributeOption) {
            $limit = ($this->config->getMoreFromBrandConfig()->getProductsLimit())
                ? : self::DEFAULT_PRODUCT_LIMIT;
            if ($limit > 100) {
                $limit = 100;
            }
            $attributeOption = explode(',', $brandAttributeOption);
            $collection      = $this->productCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter($brandAttributeCode, ['in' => $attributeOption])
                ->addFieldToFilter('entity_id', ['neq' => $product->getId()])
                ->addAttributeToFilter('status', Status::STATUS_ENABLED)
                ->setVisibility($this->productVisibility->getVisibleInSiteIds())
                ->addStoreFilter();

            $collection->getSelect()->joinLeft(
                ['inventory_table' => $this->resourceConnection->getTableName('cataloginventory_stock_item')],
                "inventory_table.product_id = e.entity_id",
                ['is_in_stock']
            );

            $collection->getSelect()->where('is_in_stock = ?', 1)
                ->orderRand()->limit($limit);

            //correct product urls in template
            foreach ($collection as $product) {
                $product->setDoNotUseCategoryId(true);
            }

            $this->productCollection = $collection;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function _toHtml()
    {
        if ($this->config->getMoreFromBrandConfig()->isEnabled()
            && ($productCollection = $this->getProductCollection())
            && is_object($productCollection)
        ) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        $title = $this->config->getMoreFromBrandConfig()->getTitle();
        if (strpos($title, self::BRAND_NAME) !== false) {
            $brandLabel = $this->getBrandLabel();
            $title      = str_replace(self::BRAND_NAME, $brandLabel, $title);
        }

        return $title;
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeToHtml()
    {
        $this->setProductCollection();

        return parent::_beforeToHtml();
    }

    /**
     * @return string
     */
    private function getBrandLabel()
    {
        $brandAttributeOption = $this->getBrandAttributeOption();
        if (strpos($brandAttributeOption, ",") === false) {
            $brandLabel = $this->brandRepository->get((int)$brandAttributeOption)->getLabel();
        } else {
            $brandLabelArray               = [];
            $brandAttributePreparedOptions = explode(',', $brandAttributeOption);
            foreach ($brandAttributePreparedOptions as $brandAttributePreparedOption) {
                $brandLabelArray[] = $this->brandRepository->get((int)$brandAttributePreparedOption)->getLabel();
            }
            $brandLabel = implode(', ', $brandLabelArray);
        }

        return $brandLabel;
    }

    /**
     * @return string
     */
    private function getBrandAttributeOption()
    {
        $product            = $this->registry->registry('product');
        $brandAttributeCode = $this->getBrandAttribute();

        return $product->getData($brandAttributeCode);
    }

    /**
     * @return string
     */
    private function getBrandAttribute()
    {
        return $this->config->getGeneralConfig()->getBrandAttribute();
    }
}
