<?php
/**
 * Class FloatApply
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_SocialShare
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\SocialShare\Model\System;

/**
 * Class FloatApply
 *
 * @category Sparsh
 * @package  Sparsh_SocialShare
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class FloatApply extends OptionArray
{
    const ALL_PAGES    = "all_pages";
    const SELECT_PAGES = "select_pages";

    /**
     * Get options
     *
     * @return array
     */
    public function getOptionHash()
    {
        return [
            self::ALL_PAGES    => __('All Pages'),
            self::SELECT_PAGES => __('Select Pages')
        ];
    }
}
