<?php
/**
 * Class ButtonSize
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
 * Class ButtonSize
 *
 * @category Sparsh
 * @package  Sparsh_SocialShare
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class ButtonSize extends OptionArray
{
    const SMALL  = "16x16";
    const MEDIUM = "32x32";
    const LARGE  = "64x64";

    /**
     * Get options
     *
     * @return array
     */
    public function getOptionHash()
    {
        return [
            self::SMALL  => __('16x16'),
            self::MEDIUM => __('32x32'),
            self::LARGE  => __('64x64'),
        ];
    }
}
