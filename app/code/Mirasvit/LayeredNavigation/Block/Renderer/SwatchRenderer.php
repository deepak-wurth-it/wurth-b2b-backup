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

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\View\Element\Template\Context;
use Magento\Swatches\Block\LayeredNavigation\RenderLayered;
use Magento\Swatches\Helper\Data as SwatchesHelperData;
use Magento\Swatches\Helper\Media as SwatchesHelperMedia;
use Mirasvit\LayeredNavigation\Api\Data\AttributeConfigInterface;
use Mirasvit\LayeredNavigation\Model\Config\Source\FilterApplyingModeSource;
use Mirasvit\LayeredNavigation\Model\ConfigProvider;
use Mirasvit\LayeredNavigation\Repository\AttributeConfigRepository;
use Mirasvit\LayeredNavigation\Service\FilterService;

/**
 * Preference (di.xml) for @see \Magento\Swatches\Block\LayeredNavigation\RenderLayered
 */
class SwatchRenderer extends RenderLayered
{
    protected $_template = 'Mirasvit_LayeredNavigation::renderer/swatchRenderer.phtml';

    private   $configProvider;

    private   $attributeConfigRepository;

    private   $filterService;

    /** @var AttributeConfigInterface */
    private $attributeConfig;

    public function __construct(
        ConfigProvider $configProvider,
        FilterService $filterService,
        AttributeConfigRepository $attributeConfigRepository,
        Context $context,
        Attribute $eavAttribute,
        AttributeFactory $layerAttribute,
        SwatchesHelperData $swatchHelper,
        SwatchesHelperMedia $mediaHelper,
        array $data = []
    ) {
        $this->configProvider            = $configProvider;
        $this->filterService             = $filterService;
        $this->attributeConfigRepository = $attributeConfigRepository;

        parent::__construct(
            $context,
            $eavAttribute,
            $layerAttribute,
            $swatchHelper,
            $mediaHelper,
            $data
        );
    }

    public function setSwatchFilter(AbstractFilter $filter): self
    {
        $this->attributeConfig = $this->attributeConfigRepository->getByAttributeCode($filter->getRequestVar());

        return parent::setSwatchFilter($filter);
    }

    public function getDisplayMode(): string
    {
        return $this->attributeConfig->getDisplayMode();
    }

    public function getSwatchFilter(): AbstractFilter
    {
        return $this->filter;
    }

    public function getFilterUniqueValue(AbstractFilter $filter): string
    {
        return $this->filterService->getFilterUniqueValue($filter);
    }

    public function getFilterRequestVar(): string
    {
        $filter = $this->getSwatchFilter();
        if (!is_object($filter)) {
            return '';
        }

        return $filter->getRequestVar();
    }

    public function isItemChecked(string $option): bool
    {
        return $this->filterService->isFilterCheckedSwatch($this->filter->getRequestVar(), $option);
    }

    public function getSwatchData(): array
    {
        $swatchData      = parent::getSwatchData();
        $attributeConfig = $this->attributeConfigRepository->getByAttributeCode($swatchData['attribute_code']);

        if ($attributeConfig) {
            $attributeConfig = $attributeConfig->getConfig();
            $swatchData      = array_merge($attributeConfig, $swatchData);
        }

        return $swatchData;
    }

    public function getRemoveUrl(string $attributeCode, int $optionId): string
    {
        return $this->buildUrl($attributeCode, $optionId);
    }

    public function getSwatchOptionLink(string $attributeCode, int $optionId): string
    {
        return $this->buildUrl($attributeCode, $optionId);
    }

    public function isApplyingMode(): bool
    {
        return $this->configProvider->isAjaxEnabled()
            && $this->configProvider->getApplyingMode() == FilterApplyingModeSource::OPTION_BY_BUTTON_CLICK;
    }
}
