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

namespace Mirasvit\Brand\Model\Config;

class Config
{
    private $brandLogoConfig;

    private $moreFromBrandConfig;

    private $brandSliderConfig;

    private $allBrandPageConfig;

    private $brandPageConfig;

    private $generalConfig;

    public function __construct(
        GeneralConfig $generalConfig,
        BrandPageConfig $brandPageConfig,
        AllBrandPageConfig $allBrandPageConfig,
        BrandSliderConfig $brandSliderConfig,
        MoreFromBrandConfig $moreFromBrandConfig,
        BrandLogoConfig $brandLogoConfig
    ) {
        $this->generalConfig       = $generalConfig;
        $this->brandPageConfig     = $brandPageConfig;
        $this->allBrandPageConfig  = $allBrandPageConfig;
        $this->brandSliderConfig   = $brandSliderConfig;
        $this->moreFromBrandConfig = $moreFromBrandConfig;
        $this->brandLogoConfig     = $brandLogoConfig;
    }

    public function getGeneralConfig(): GeneralConfig
    {
        return $this->generalConfig;
    }

    public function getBrandPageConfig(): BrandPageConfig
    {
        return $this->brandPageConfig;
    }

    public function getAllBrandPageConfig(): AllBrandPageConfig
    {
        return $this->allBrandPageConfig;
    }

    public function getBrandSliderConfig(): BrandSliderConfig
    {
        return $this->brandSliderConfig;
    }

    public function getMoreFromBrandConfig(): MoreFromBrandConfig
    {
        return $this->moreFromBrandConfig;
    }

    public function getBrandLogoConfig(): BrandLogoConfig
    {
        return $this->brandLogoConfig;
    }
}
