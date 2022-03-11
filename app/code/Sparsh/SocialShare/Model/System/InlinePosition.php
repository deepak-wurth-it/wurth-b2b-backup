<?php
/**
 * Class InlinePosition
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
 * Class InlinePosition
 *
 * @category Sparsh
 * @package  Sparsh_SocialShare
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class InlinePosition extends OptionArray
{
    const TOP_CONTENT    = "top_content";
    const BOTTOM_CONTENT = "bottom_content";
    const NONE = "none";

    /**
     * Get options
     *
     * @return array
     */
    public function getOptionHash()
    {
        return [
            self::TOP_CONTENT    => __('Top Content'),
            self::BOTTOM_CONTENT => __('Bottom Content'),
            self::NONE => __('None'),
        ];
    }
}
