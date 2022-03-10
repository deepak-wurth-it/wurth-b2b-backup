<?php
/**
 * Class ColorSchema
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
 * Class ColorSchema
 *
 * @category Sparsh
 * @package  Sparsh_SocialShare
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class ColorSchema extends OptionArray
{
    const ORIGINAL = "default";
    const CUSTOM = "custom";

    /**
     * Get options
     *
     * @return array
     */
    public function getOptionHash()
    {
        return [
            self::ORIGINAL => __('Default Social Icon'),
            self::CUSTOM => __('Custom Color Icon')
        ];
    }
}
