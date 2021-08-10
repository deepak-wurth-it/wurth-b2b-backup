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

namespace Mirasvit\LayeredNavigation\Service;

use Mirasvit\LayeredNavigation\Model\Config;
use Mirasvit\LayeredNavigation\Model\ConfigProvider;

class CssCreatorService
{
    private $horizontalFiltersConfig;

    private $highlightConfigProvider;

    private $configProvider;

    private $stateBarConfigProvider;

    public function __construct(
        ConfigProvider $configProvider,
        Config\HorizontalBarConfigProvider $horizontalFiltersConfig,
        Config\HighlightConfigProvider $highlightConfigProvider,
        Config\StateBarConfigProvider $stateBarConfigProvider
    ) {
        $this->configProvider          = $configProvider;
        $this->horizontalFiltersConfig = $horizontalFiltersConfig;
        $this->highlightConfigProvider = $highlightConfigProvider;
        $this->stateBarConfigProvider  = $stateBarConfigProvider;
    }

    public function getCssContent(int $storeId): string
    {
        $css = '';
        $css = $this->getHorizontalFiltersCss($storeId, $css);
        $css = $this->getHighlightColorCss($storeId, $css);
        $css = $this->getFilterClearBlockCss($storeId, $css);

        $css = $this->getDisplayOptionsCss($storeId, $css);
        $css = $this->getShowOpenedFiltersCss($storeId, $css);

        return $css;
    }

    private function getHorizontalFiltersCss(int $storeId, string $css): string
    {
        if ($hideHorizontalFiltersValue = $this->horizontalFiltersConfig->getHideHorizontalFiltersValue()) {
            $hideHorizontalFiltersValue = $hideHorizontalFiltersValue . 'px'; //delete px if exist

            $css .= '/* Hide horizontal filters if screen size is less than (px) - begin */';
            $css .= '@media all and (max-width: ' . $hideHorizontalFiltersValue . 'px) {';
            $css .= '.mst-nav__horizontal-bar .block-subtitle.filter-subtitle {display: none !important;} ';
            $css .= '.mst-nav__horizontal-bar .filter-options {display: none !important;} ';
            $css .= '} ';
            $css .= '/* Hide horizontal filters if screen size is less than (px) - end */';
        }

        if (count($this->horizontalFiltersConfig->getFilters()) == 0 && $this->stateBarConfigProvider->isHorizontalPosition() == false) {
            $css .= '.mst-nav__horizontal-bar {display:none}';
        }

        return $css;
    }

    private function getFilterClearBlockCss(int $storeId, string $css): string
    {
        if ($this->stateBarConfigProvider->isHorizontalPosition()) {
            $css .= '/* Show horizontal clear filter panel - begin */';
            $css .= '.navigation-horizontal {display: block !important;} ';
            $css .= '@media all and (mix-width: 767px) {';
            $css .= '.navigation-horizontal .block-actions.filter-actions {display: block !important;} ';
            $css .= '} ';
            $css .= '@media all and (max-width: 767px) {';
            $css .= '.navigation-horizontal .block-title.filter-title {display: none !important;} ';
            $css .= '} ';
            $css .= '.sidebar .block-actions.filter-actions {display: none;} ';
            $css .= '/* Show horizontal clear filter panel - end */';
        } else {
            $css .= '.navigation-horizontal .block-actions.filter-actions {display: none;} ';
        }

        if ($this->stateBarConfigProvider->isHidden()) {
            $css .= '.sidebar .block-actions.filter-actions {display: none;} ';
        }

        return $css;
    }

    private function getHighlightColorCss(int $storeId, string $css): string
    {
        $color = $this->highlightConfigProvider->getColor($storeId);

        $css .= $this->getStyle('.mst-nav__label .mst-nav__label-item._highlight a', [
            'color' => $color,
        ]);

        //        $css .= '.item .m-navigation-link-highlight { color:' . $color . '; } ';
        //        $css .= '.m-navigation-highlight-swatch .swatch-option.selected { outline: 2px solid ' . $color . '; } ';
        //        $css .= '.m-navigation-filter-item .swatch-option.image:not(.disabled):hover { outline: 2px solid'
        //            . $color . '; border: 1px solid #fff; } ';
        //        $css .= '.swatch-option.image.m-navigation-highlight-swatch { outline: 2px solid'
        //            . $color . '; 1px solid #fff; } ';
        //        $css .= '.m-navigation-swatch .swatch-option:not(.disabled):hover { outline: 2px solid'
        //            . $color . '; border: 1px solid #fff;  color: #333; } ';
        //        $css .= '.m-navigation-swatch .m-navigation-highlight-swatch .swatch-option { outline: 2px solid'
        //            . $color . '; border: 1px solid #fff;  color: #333; } ';
        //

        return $css;
    }

    private function getDisplayOptionsCss(int $storeId, string $css): string
    {
        if ($backgroundColor = $this->configProvider->getDisplayOptionsBackgroundColor()) {
            $css
                .= '.checkbox input[type="checkbox"]:checked + label::before,
                      .checkbox input[type="radio"]:checked + label::before { background-color:'
                . $backgroundColor . '; } ';
        }
        if ($borderColor = $this->configProvider->getDisplayOptionsBorderColor()) {
            $css
                .= '.checkbox input[type="checkbox"]:checked + label::before,
                      .checkbox input[type="radio"]:checked + label::before { border-color:'
                . $borderColor . '; } ';
        }
        if ($checkedLabelColor = $this->configProvider->getDisplayOptionsCheckedLabelColor()) {
            $css
                .= '.checkbox input[type="checkbox"]:checked+label::after,
                     .checkbox input[type="radio"]:checked+label::after { color:'
                . $checkedLabelColor . '; } ';
        }

        return $css;
    }

    private function getShowOpenedFiltersCss(int $storeId, string $css): string
    {
        if ($isShowOpenedFilters = $this->configProvider->isOpenFilter()) {
            $css .= '.sidebar .filter-options .filter-options-content { display: block; } ';
        }

        return $css;
    }

    private function getStyle(string $selector, array $styles): string
    {
        $arr = [];

        foreach ($styles as $key => $value) {
            if ($value) {
                $arr[] = $key . ': ' . $value . ';';
            }
        }

        return $selector . '{' . implode($arr) . '}';
    }
}
