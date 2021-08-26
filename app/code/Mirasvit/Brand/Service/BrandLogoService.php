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

namespace Mirasvit\Brand\Service;

use Magento\Framework\View\Element\BlockFactory;
use Mirasvit\Brand\Api\Data\BrandInterface;
use Mirasvit\Brand\Api\Data\BrandPageInterface;
use Mirasvit\Brand\Block\Logo;
use Mirasvit\Brand\Model\Config\Config;
use Mirasvit\Brand\Repository\BrandPageRepository;
use Mirasvit\Brand\Repository\BrandRepository;

class BrandLogoService
{
    const BRAND_TITLE_PATTERN             = '{title}';
    const BRAND_SMALL_IMAGE_PATTERN       = '{small_image}';
    const BRAND_IMAGE_PATTERN             = '{image}';
    const BRAND_DESCRIPTION_PATTERN       = '{description}';
    const BRAND_SHORT_DESCRIPTION_PATTERN = '{short_description}';

    private static $brandPageList;

    private static $brandList;

    /** @var BrandPageInterface */
    private $brandPage;

    /** @var BrandInterface */
    private $brand;

    private $brandPageRepository;

    private $brandRepository;

    private $config;

    private $brandUrlService;

    private $imageUrlService;

    private $blockFactory;

    public function __construct(
        BlockFactory $blockFactory,
        ImageUrlService $imageUrlService,
        BrandPageRepository $brandPageRepository,
        BrandRepository $brandRepository,
        BrandUrlService $brandUrlService,
        Config $config
    ) {
        $this->blockFactory        = $blockFactory;
        $this->imageUrlService     = $imageUrlService;
        $this->brandPageRepository = $brandPageRepository;
        $this->brandRepository     = $brandRepository;
        $this->brandUrlService     = $brandUrlService;
        $this->config              = $config;
    }

    public function getLogoHtml(): string
    {
        return (string)$this->blockFactory
            ->createBlock(Logo::class)
            ->setTemplate('Mirasvit_Brand::logo/logo.phtml')
            ->toHtml();
    }

    public function getLogoImageUrl(string $imageType = null): string
    {
        $imageType = $imageType ? false : 'thumbnail';

        return $this->imageUrlService->getImageUrl($this->brandPage->getLogo(), $imageType);
    }

    public function getBrandTitle(): string
    {
        return $this->brandPage->getBrandTitle();
    }

    public function getBrandUrl(): string
    {
        return $this->brandUrlService->getBrandUrl($this->brand);
    }

    public function getBrandDescription(): string
    {
        return $this->brandPage->getBrandDescription();
    }

    public function getBrandShortDescription(): string
    {
        return $this->brandPage->getBrandShortDescription();
    }

    public function getLogoTooltipContent(string $tooltip): string
    {
        $tooltipContent = '';
        $style          = '';
        if ($tooltip) {
            if ($tooltipMaxImageWidth = $this->config->getBrandLogoConfig()->getTooltipMaxImageWidth()) {
                $style = 'style="max-width: ' . $tooltipMaxImageWidth . 'px !important;"';
            }
            $search  = [
                BrandLogoService::BRAND_TITLE_PATTERN,
                BrandLogoService::BRAND_IMAGE_PATTERN,
                BrandLogoService::BRAND_SMALL_IMAGE_PATTERN,
                BrandLogoService::BRAND_DESCRIPTION_PATTERN,
                BrandLogoService::BRAND_SHORT_DESCRIPTION_PATTERN,
            ];
            $replace = [
                $this->getBrandTitle(),
                '<img ' . $style . 'src="' . $this->getLogoImageUrl() . '">',
                '<img src="' . $this->getLogoImageUrl() . '">',
                $this->getPreparedText($this->getBrandDescription()),
                $this->getPreparedText($this->getBrandShortDescription()),
            ];

            $tooltipContent .= str_replace($search, $replace, $tooltip);
        }

        return $tooltipContent;
    }

    public function setBrandDataByOptionId(int $optionId): void
    {
        $this->setBrandData();

        if (self::$brandPageList && isset(self::$brandPageList[$optionId])) {
            $this->brandPage = self::$brandPageList[$optionId];
            $this->brand     = self::$brandList[$optionId];
        } else {
            $this->brandPage = $this->brandPageRepository->create();
            $this->brand     = $this->brandRepository->create();
        }
    }

    private function getPreparedText(string $text): string
    {
        return str_replace(['"', "'"], ['&quot;', '&apos;'], $text);
    }

    private function setBrandData(): void
    {
        if (self::$brandPageList != null) {
            return;
        }

        self::$brandPageList = [];
        self::$brandList     = [];

        foreach ($this->brandPageRepository->getCollection() as $brandPage) {
            self::$brandPageList[$brandPage->getAttributeOptionId()] = $brandPage;
        }

        foreach ($this->brandRepository->getFullList() as $brand) {
            self::$brandList[$brand->getPage()->getAttributeOptionId()] = $brand;
        }
    }
}
