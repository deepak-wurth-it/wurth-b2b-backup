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

namespace Mirasvit\LayeredNavigation\Block\Renderer\Element;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\View\Element\Template;
use Mirasvit\LayeredNavigation\Model\Config\SizeLimiterConfigProvider;
use Mirasvit\LayeredNavigation\Model\Config\Source\SizeLimiterDisplayModeSource;

class SizeLimiterElement extends Template
{
    private $sizeLimiterConfigProvider;

    /** @var FilterInterface */
    private $filter;

    public function __construct(
        SizeLimiterConfigProvider $sizeLimiterConfigProvider,
        Template\Context $context,
        array $data = []
    ) {
        $this->sizeLimiterConfigProvider = $sizeLimiterConfigProvider;

        parent::__construct($context, $data);
    }

    public function setFilter(FilterInterface $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    public function getAttributeCode(): string
    {
        return (string)$this->filter->getRequestVar();
    }

    public function isScrollMode(): bool
    {
        return $this->sizeLimiterConfigProvider->getDisplayMode() == SizeLimiterDisplayModeSource::MODE_SCROLL
            && $this->getScrollHeight();
    }

    public function isShowHideMode(): bool
    {
        return $this->sizeLimiterConfigProvider->getDisplayMode() == SizeLimiterDisplayModeSource::MODE_SHOW_HIDE
            && $this->getLinkLimit()
            && $this->filter->getItemsCount() > $this->getLinkLimit();
    }

    public function getScrollHeight(): int
    {
        return $this->sizeLimiterConfigProvider->getScrollHeight();
    }

    public function getLinkLimit(): int
    {
        return $this->sizeLimiterConfigProvider->getLinkLimit();
    }

    public function getTextLess(): string
    {
        return $this->sizeLimiterConfigProvider->getTextLess();
    }

    public function getTextMore(): string
    {
        return $this->sizeLimiterConfigProvider->getTextMore();
    }
}
