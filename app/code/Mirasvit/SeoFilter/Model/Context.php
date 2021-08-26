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
 * @package   mirasvit/module-seo-filter
 * @version   1.1.5
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\SeoFilter\Model;

use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory as EntityAttributeOptionCollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Context
{
    private $productResource;

    private $storeManager;

    private $attributeOptionCollectionFactory;

    private $urlBuilder;

    private $registry;

    /** @var \Magento\Framework\App\Request\Http */
    private $request;

    public function __construct(
        ProductResource $productResource,
        StoreManagerInterface $storeManager,
        EntityAttributeOptionCollectionFactory $entityAttributeOptionCollectionFactory,
        UrlInterface $urlBuilder,
        Registry $registry,
        RequestInterface $request
    ) {
        $this->productResource                  = $productResource;
        $this->storeManager                     = $storeManager;
        $this->attributeOptionCollectionFactory = $entityAttributeOptionCollectionFactory;
        $this->urlBuilder                       = $urlBuilder;
        $this->registry                         = $registry;
        $this->request                          = $request;
    }


    public function getStoreId(): int
    {
        return (int)$this->storeManager->getStore()->getId();
    }


    public function getAttribute(string $code): ?\Magento\Catalog\Model\ResourceModel\Eav\Attribute
    {
        $attribute = $this->productResource->getAttribute($code);

        return $attribute ? $attribute : null;
    }

    public function isDecimalAttribute(string $attribute): bool
    {
        $attr = $this->getAttribute($attribute);

        return $attr && $this->getAttribute($attribute)->getFrontendInput() == 'price';
    }

    public function getAttributeOption(int $attributeId, int $optionId): ?\Magento\Eav\Model\Entity\Attribute\Option
    {
        /** @var \Magento\Eav\Model\Entity\Attribute\Option $item */
        $item = $this->attributeOptionCollectionFactory->create()
            ->setStoreFilter($this->getStoreId(), true)
            ->setAttributeFilter($attributeId)
            ->setIdFilter($optionId)
            ->getFirstItem();

        return $item->getId() ? $item : null;
    }

    public function getUrlBuilder(): UrlInterface
    {
        return $this->urlBuilder;
    }

    public function getCurrentCategory(): ?\Magento\Catalog\Model\Category
    {
        $category = $this->registry->registry('current_category');

        return $category ? $category : null;
    }

    public function getRequest(): \Magento\Framework\App\Request\Http
    {
        return $this->request;
    }
}
