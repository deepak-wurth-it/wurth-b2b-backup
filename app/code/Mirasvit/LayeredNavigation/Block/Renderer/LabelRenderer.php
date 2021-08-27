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

use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Mirasvit\LayeredNavigation\Model\Config\HighlightConfigProvider;
use Mirasvit\LayeredNavigation\Model\Config\SeoConfigProvider;
use Mirasvit\LayeredNavigation\Model\Config\Source\FilterApplyingModeSource;
use Mirasvit\LayeredNavigation\Model\ConfigProvider;
use Mirasvit\LayeredNavigation\Service\FilterService;

class LabelRenderer extends AbstractRenderer
{
    protected $_template = 'Mirasvit_LayeredNavigation::renderer/labelRenderer.phtml';

    private   $filterService;

    private   $configProvider;

    private   $highlightConfigProvider;

    public function __construct(
        FilterService $filterService,
        ConfigProvider $configProvider,
        HighlightConfigProvider $highlightConfigProvider,
        SeoConfigProvider $seoConfigProvider,
        Template\Context $context,
        array $data = []
    ) {
        $this->filterService           = $filterService;
        $this->configProvider          = $configProvider;
        $this->highlightConfigProvider = $highlightConfigProvider;

        parent::__construct($seoConfigProvider, $context, $data);
    }

    public function isFilterItemChecked(Item $filterItem, bool $multiselect = false): bool
    {
        return $this->filterService->isFilterItemChecked($filterItem, $multiselect);
    }

    public function isAjaxEnabled(): bool
    {
        return $this->configProvider->isAjaxEnabled();
    }

    public function isMultiselectEnabled(): bool
    {
        return $this->configProvider->isMultiselectEnabled();
    }

    public function getImageUrl(Item $filterItem): ?string
    {
        foreach ($this->attributeConfig->getOptionsConfig() as $optionConfig) {
            if ($optionConfig->getOptionId() === $filterItem->getValueString()) {
                if ($optionConfig->getImagePath()) {
                    return $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                        . 'tmp/catalog/product/' . $optionConfig->getImagePath();
                }
            }
        }

        return null;
    }

    public function isFullWidthImage(Item $filterItem): bool
    {
        foreach ($this->attributeConfig->getOptionsConfig() as $optionConfig) {
            if ($optionConfig->getOptionId() === $filterItem->getValueString()) {
                return $optionConfig->isFullImageWidth();
            }
        }

        return false;
    }

    public function isHighlightEnabled(): bool
    {
        return $this->highlightConfigProvider->isEnabled($this->storeId);
    }

    public function getFilterItemDisplayMode(): string
    {
        return $this->configProvider->getFilterItemDisplayMode();
    }

    public function isApplyingMode(): bool
    {
        return $this->isAjaxEnabled() && $this->configProvider->getApplyingMode() == FilterApplyingModeSource::OPTION_BY_BUTTON_CLICK;
    }
}
