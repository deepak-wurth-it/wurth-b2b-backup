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

namespace Mirasvit\Brand\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;
use Mirasvit\Brand\Api\Data\BrandPageInterface;
use Mirasvit\Brand\Model\Config\Config;
use Mirasvit\Brand\Model\Config\Source\BrandSliderOrder;
use Mirasvit\Brand\Repository\BrandPageRepository;
use Mirasvit\Brand\Repository\BrandRepository;
use Mirasvit\Brand\Service\BrandAttributeService;
use Mirasvit\Brand\Service\BrandUrlService;
use Mirasvit\Brand\Service\ImageUrlService;

class BrandSlider extends Template implements BlockInterface
{
    private $brandPageRepository;

    private $brandRepository;

    private $sliderConfig;

    private $brandAttributeService;

    private $storeId;

    private $imageUrlService;

    private $brandUrlService;

    public function __construct(
        Context $context,
        BrandPageRepository $brandPageRepository,
        BrandRepository $brandRepository,
        Config $config,
        BrandAttributeService $brandAttributeService,
        ImageUrlService $imageUrlService,
        BrandUrlService $brandUrlService,
        array $data = []
    ) {
        $this->brandPageRepository   = $brandPageRepository;
        $this->brandRepository       = $brandRepository;
        $this->sliderConfig          = $config->getBrandSliderConfig();
        $this->brandAttributeService = $brandAttributeService;
        $this->storeId               = $context->getStoreManager()->getStore()->getStoreId();
        $this->imageUrlService       = $imageUrlService;
        $this->brandUrlService       = $brandUrlService;

        parent::__construct($context, $data);
    }

    /** @return BrandPageInterface[] */
    public function getSliderItems(): array
    {
        $attributeId = $this->brandAttributeService->getBrandAttributeId();

        $collection = $this->brandPageRepository->getCollection()
            ->addStoreFilter($this->storeId)
            ->addFieldToFilter(BrandPageInterface::ATTRIBUTE_ID, $attributeId)
            ->addFieldToFilter(BrandPageInterface::IS_SHOW_IN_BRAND_SLIDER, 1);

        if ($this->getOrder() == BrandSliderOrder::SLIDER_POSITION_ORDER) {
            $collection->setOrder(
                BrandPageInterface::SLIDER_POSITION,
                'asc'
            );
        } else {
            $collection->setOrder(
                BrandPageInterface::BRAND_TITLE,
                'asc'
            );
        }

        return $collection->getItems();
    }

    public function getImageUrl(string $imageName): string
    {
        return $this->imageUrlService->getImageUrl($imageName);
    }

    public function getBrandUrl(BrandPageInterface $brandPage): string
    {
        $brand = $this->brandRepository->get($brandPage->getAttributeOptionId());

        return $this->brandUrlService->getBrandUrl($brand);
    }

    public function getItemsLimit(): int
    {
        return $this->hasData('ItemsLimit')
            ? (int)$this->getData('ItemsLimit')
            : $this->sliderConfig->getItemsLimit();
    }

    public function getOrder(): int
    {
        return $this->hasData('Order')
            ? (int)$this->getData('Order')
            : $this->sliderConfig->getOrder();
    }

    public function isShowTitle(): bool
    {
        return $this->hasData('isShowTitle')
            ? (bool)$this->getData('isShowTitle')
            : $this->sliderConfig->isShowTitle();
    }

    public function getTitleText(): string
    {
        return $this->hasData('TitleText')
            ? (string)$this->getData('TitleText')
            : $this->sliderConfig->getTitleText();
    }

    public function getTitleTextColor(): string
    {
        return $this->hasData('TitleTextColor')
            ? (string)$this->getData('TitleTextColor')
            : $this->sliderConfig->getTitleTextColor();
    }

    public function getTitleBackgroundColor(): string
    {
        return $this->hasData('TitleBackgroundColor')
            ? (string)$this->getData('TitleBackgroundColor')
            : $this->sliderConfig->getTitleBackgroundColor();
    }

    public function isShowBrandLabel(): bool
    {
        return $this->hasData('isShowBrandLabel')
            ? (bool)$this->getData('isShowBrandLabel')
            : $this->sliderConfig->isShowBrandLabel();
    }

    public function getBrandLabelColor(): string
    {
        return $this->hasData('BrandLabelColor')
            ? (string)$this->getData('BrandLabelColor')
            : $this->sliderConfig->getBrandLabelColor();
    }

    public function isShowButton(): bool
    {
        return $this->hasData('isShowButton')
            ? (bool)$this->getData('isShowButton')
            : $this->sliderConfig->isShowButton();
    }

    public function isShowPagination(): bool
    {
        return $this->hasData('isShowPagination')
            ? (bool)$this->getData('isShowPagination')
            : $this->sliderConfig->isShowPagination();
    }

    public function isAutoPlay(): bool
    {
        return $this->hasData('isAutoPlay')
            ? (bool)$this->getData('isAutoPlay')
            : $this->sliderConfig->isAutoPlay();
    }

    public function isAutoPlayLoop(): bool
    {
        return $this->hasData('isAutoPlayLoop')
            ? (bool)$this->getData('isAutoPlayLoop')
            : $this->sliderConfig->isAutoPlayLoop();
    }

    public function getAutoPlayInterval(): int
    {
        return $this->hasData('AutoPlayInterval')
            ? (int)$this->getData('AutoPlayInterval')
            : $this->sliderConfig->getAutoPlayInterval();
    }

    public function getPauseOnHover(): int
    {
        return $this->hasData('PauseOnHover')
            ? (int)$this->getData('PauseOnHover')
            : $this->sliderConfig->getPauseOnHover();
    }

    public function getSliderWidth(): int
    {
        return $this->hasData('SliderWidth')
            ? (int)$this->getData('SliderWidth')
            : $this->sliderConfig->getSliderWidth();
    }

    public function getSliderImageWidth(): int
    {
        return $this->hasData('SliderImageWidth')
            ? (int)$this->getData('SliderImageWidth')
            : $this->sliderConfig->getSliderImageWidth();
    }

    public function getSpacingBetweenImages(): int
    {
        return $this->hasData('SpacingBetweenImages')
            ? (int)$this->getData('SpacingBetweenImages')
            : $this->sliderConfig->getSpacingBetweenImages();
    }

    public function getInactivePagingColor(): string
    {
        return $this->hasData('InactivePagingColor')
            ? (string)$this->getData('InactivePagingColor')
            : $this->sliderConfig->getInactivePagingColor();
    }

    public function getActivePagingColor(): string
    {
        return $this->hasData('ActivePagingColor')
            ? (string)$this->getData('ActivePagingColor')
            : $this->sliderConfig->getActivePagingColor();
    }

    public function getHoverPagingColor(): string
    {
        return $this->hasData('HoverPagingColor')
            ? (string)$this->getData('HoverPagingColor')
            : $this->sliderConfig->getHoverPagingColor();
    }

    public function getNavigationButtonsColor(): string
    {
        return $this->hasData('NavigationButtonsColor')
            ? (string)$this->getData('NavigationButtonsColor')
            : $this->sliderConfig->getNavigationButtonsColor();
    }
}
