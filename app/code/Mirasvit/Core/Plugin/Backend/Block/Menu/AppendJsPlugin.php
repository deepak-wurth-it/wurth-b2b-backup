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
 * @package   mirasvit/module-core
 * @version   1.2.122
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Core\Plugin\Backend\Block\Menu;

use Magento\Backend\Block\Menu;

class AppendJsPlugin
{
    /**
     * @param Menu $subject
     * @param string $html
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterToHtml(Menu $subject, $html)
    {
        $js = $subject->getLayout()->createBlock(\Magento\Backend\Block\Template::class)
            ->setTemplate('Mirasvit_Core::backend/menu/js.phtml')
            ->toHtml();

        return $html . $js;
    }
}