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

namespace Mirasvit\Brand\Api\Data;

interface BrandPageInterface
{
    const TABLE_NAME = 'mst_brand_page';

    const ID                      = 'brand_page_id';
    const ATTRIBUTE_OPTION_ID     = 'attribute_option_id';
    const ATTRIBUTE_ID            = 'attribute_id';
    const IS_ACTIVE               = 'is_active';
    const URL_KEY                 = 'url_key';
    const LOGO                    = 'logo';
    const BRAND_TITLE             = 'brand_title';
    const BRAND_DESCRIPTION       = 'brand_description';
    const META_TITLE              = 'meta_title';
    const KEYWORD                 = 'meta_keyword';
    const META_DESCRIPTION        = 'meta_description';
    const ROBOTS                  = 'robots';
    const CANONICAL               = 'canonical';
    const BANNER_ALT              = 'banner_alt';
    const BANNER_TITLE            = 'banner_title';
    const BANNER                  = 'banner';
    const BANNER_POSITION         = 'banner_position';
    const IS_SHOW_IN_BRAND_SLIDER = 'is_show_in_brand_slider';
    const SLIDER_POSITION         = 'slider_position';
    const BRAND_SHORT_DESCRIPTION = 'brand_short_description';

    const ATTRIBUTE_CODE = 'attribute_code';
    const BRAND_NAME     = 'brand_name';

    public function getId(): ?int;

    public function getAttributeOptionId(): int;

    public function setAttributeOptionId(int $value): self;

    public function getAttributeId(): int;

    public function setAttributeId(int $value): self;

    public function getIsActive(): bool;

    public function setIsActive(bool $value): self;

    public function getLogo(): string;

    public function setLogo(string $value): self;

    public function getBrandTitle(): string;

    public function setBrandTitle(string $value): self;

    public function getUrlKey(): string;

    public function setUrlKey(string $value): self;

    public function getBrandDescription(): string;

    public function setBrandDescription(string $value): self;

    public function getMetaTitle(): string;

    public function setMetaTitle(string $value): self;

    public function getKeyword(): string;

    public function setKeyword(string $value): self;

    public function getMetaDescription(): string;

    public function setMetaDescription(string $value): self;

    public function getRobots(): string;

    public function setRobots(string $value): self;

    public function getCanonical(): string;

    public function setCanonical(string $value): self;

    public function getAttributeCode(): string;

    public function getBrandName(): string;

    public function setBrandName(string $value): self;

    public function getBannerAlt(): string;

    public function setBannerAlt(string $value): self;

    public function getBannerTitle(): string;

    public function setBannerTitle(string $value): self;

    public function getBanner(): string;

    public function setBanner(string $value): self;

    public function getBannerPosition(): string;

    public function setBannerPosition(string $value): self;

    public function getBrandShortDescription(): string;

    public function setBrandShortDescription(string $value): self;
}
