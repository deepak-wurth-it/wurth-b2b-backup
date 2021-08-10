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

namespace Mirasvit\LayeredNavigation\Block\Renderer;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Framework\View\Element\Template;
use Mirasvit\LayeredNavigation\Api\Data\AttributeConfigInterface;
use Mirasvit\LayeredNavigation\Model\Config\SeoConfigProvider;

abstract class AbstractRenderer extends Template
{
    /** @var FilterInterface */
    protected $filter;

    /** @var AttributeConfigInterface */
    protected $attributeConfig;

    protected $seoConfigProvider;

    protected $storeId;

    public function __construct(
        SeoConfigProvider $seoConfigProvider,
        Template\Context $context,
        array $data = []
    ) {
        $this->storeId           = (int)$context->getStoreManager()->getStore()->getId();
        $this->seoConfigProvider = $seoConfigProvider;

        parent::__construct($context, $data);
    }

    public function setFilterData(FilterInterface $filter, AttributeConfigInterface $attributeConfig): self
    {
        $this->filter          = $filter;
        $this->attributeConfig = $attributeConfig;

        return $this;
    }

    public function getFilter(): FilterInterface
    {
        return $this->filter;
    }

    /** @return Item[] */
    public function getFilterItems(): array
    {
        return $this->filter->getItems();
    }

    public function getAttributeCode(): string
    {
        return (string)$this->filter->getRequestVar();
    }

    public function getItemId(Item $filterItem): string
    {
        return 'm_' . $this->getFilter()->getRequestVar() . '[' . $filterItem->getValueString() . ']';
    }

    public function getRelAttributeValue(): string
    {
        return $this->seoConfigProvider->getRelAttribute();
    }

    public function getCountElement(Item $filterItem): string
    {
        /** @var Template $block */
        $block = $this->_layout->createBlock(Template::class);
        $block->setTemplate('Mirasvit_LayeredNavigation::renderer/element/count.phtml')
            ->setData('count', $filterItem->getData('count'));

        return $block->toHtml();
    }

    public function getSizeLimiterElement(): string
    {
        /** @var Element\SizeLimiterElement $block */
        $block = $this->_layout->createBlock(Element\SizeLimiterElement::class);
        $block->setFilter($this->filter)
            ->setTemplate('Mirasvit_LayeredNavigation::renderer/element/sizeLimiter.phtml');

        return $block->toHtml();
    }

    public function getSearchBoxElement(): string
    {
        /** @var Element\SearchBoxElement $block */
        $block = $this->_layout->createBlock(Element\SearchBoxElement::class);
        $block->setFilter($this->filter)
            ->setAttributeConfig($this->attributeConfig)
            ->setTemplate('Mirasvit_LayeredNavigation::renderer/element/searchBox.phtml');

        return $block->toHtml();
    }
}
