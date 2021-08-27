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

namespace Mirasvit\LayeredNavigation\Plugin\Frontend;

use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Result\Page;

/**
 * @see \Magento\Framework\View\Result\Page::renderResult()
 */
class AddThemeClassToBodyPlugin
{
    private $design;

    public function __construct(
        DesignInterface $design
    ) {
        $this->design = $design;
    }

    public function beforeRenderResult(Page $subject, object $response): array
    {
        foreach ($this->design->getDesignTheme()->getInheritedThemes() as $theme) {
            $cssClass = $this->getThemeClass($theme);

            $subject->getConfig()->addBodyClass($cssClass);
        }

        return [$response];
    }

    private function getThemeClass(ThemeInterface $theme): string
    {
        $code = (string)$theme->getCode();

        return 'mst-nav__theme-' . preg_replace('/[^a-z0-9]/', '-', strtolower($code));
    }
}
