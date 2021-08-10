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

use Magento\Framework\View\Element\Template;
use Mirasvit\LayeredNavigation\Api\Data\AttributeConfigInterface;
use Mirasvit\LayeredNavigation\Model\Config\SeoConfigProvider;
use Mirasvit\LayeredNavigation\Model\ConfigProvider;
use Mirasvit\LayeredNavigation\Service\SliderService;

class SliderRenderer extends AbstractRenderer
{
    protected $_template = 'Mirasvit_LayeredNavigation::renderer/sliderRenderer.phtml';

    private   $configProvider;

    private   $sliderService;

    public function __construct(
        ConfigProvider $configProvider,
        SliderService $sliderService,
        SeoConfigProvider $seoConfigProvider,
        Template\Context $context,
        array $data = []
    ) {
        $this->configProvider = $configProvider;
        $this->sliderService  = $sliderService;

        parent::__construct($seoConfigProvider, $context, $data);
    }

    public function isSlider(): bool
    {
        return in_array($this->attributeConfig->getDisplayMode(), [
            AttributeConfigInterface::DISPLAY_MODE_SLIDER,
            AttributeConfigInterface::DISPLAY_MODE_SLIDER_FROM_TO,
        ]);
    }

    public function isFromTo(): bool
    {
        return in_array($this->attributeConfig->getDisplayMode(), [
            AttributeConfigInterface::DISPLAY_MODE_FROM_TO,
            AttributeConfigInterface::DISPLAY_MODE_SLIDER_FROM_TO,
        ]);
    }

    public function getSeparator(): string
    {
        return $this->configProvider->getSeoFiltersUrlFormat() === 'attr_options' ? '-' : ':';
    }

    public function getValueTemplate(): string
    {
        if ($this->getAttributeCode() === 'price') {
            $cs = $this->_storeManager->getStore()->getCurrentCurrency()
                ->getCurrencySymbol();

            return $cs . '{value.2}';
        }

        return $this->attributeConfig->getValueTemplate() ? $this->attributeConfig->getValueTemplate() : '{value}';
    }

    public function getSliderData(): array
    {
        return $this->filter->getSliderData($this->getSliderUrl());
    }

    public function getSliderUrl(): string
    {
        return $this->sliderService->getSliderUrl($this->filter, $this->getSliderParamTemplate());
    }

    public function getSliderParamTemplate(): string
    {
        return $this->sliderService->getParamTemplate($this->filter);
    }
}
