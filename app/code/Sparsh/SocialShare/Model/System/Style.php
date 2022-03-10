<?php
/**
 * Class Style
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
 * Class Style
 *
 * @category Sparsh
 * @package  Sparsh_SocialShare
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */

class Style extends OptionArray
{
    const HORIZONTAL = "horizontal";
    const VERTICAL   = "vertical";

    /**
     * Get options
     *
     * @return array
     */
    public function getOptionHash()
    {
        return [
            self::HORIZONTAL => __('Horizontal'),
            self::VERTICAL   => __('Vertical'),
        ];
    }
}
