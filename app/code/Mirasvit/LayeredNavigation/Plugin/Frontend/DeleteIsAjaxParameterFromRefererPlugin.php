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

/**
 * Delete IsAjax parameter from referer url
 * @see \Magento\Store\App\Response\Redirect::getRedirectUrl()
 */

class DeleteIsAjaxParameterFromRefererPlugin
{

    /**
     * Delete IsAjax parameter from referer url for redirect in response
     *
     * @param   \Magento\Store\App\Response\Redirect    $subject
     * @param   string                                  $result
     * @return  string                                  $result
     *
     * @throws  \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetRedirectUrl($subject, $result)
    {
        $map = [
            '&amp;'         => '&',
            '?isAjax=1&'    => '?',
            '?isAjax=1'     => '',
            '&isAjax=1'     => '',
            '?isAjax=true&' => '?',
            '?isAjax=true'  => '',
            '&isAjax=true'  => '',
        ];

        foreach ($map as $search => $replace) {
            $result = str_replace($search, $replace, $result);
        }

        return $result;
    }
}
