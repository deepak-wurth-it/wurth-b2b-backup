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

namespace Mirasvit\LayeredNavigation\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class LinkRelSource implements OptionSourceInterface
{
    const FOLLOW    = 'follow';
    const NO_FOLLOW = 'nofollow';

    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::FOLLOW,
                'label' => __('Follow rel="follow"'),
            ],
            [
                'value' => self::NO_FOLLOW,
                'label' => __('No Follow rel="nofollow"'),
            ],
        ];
    }
}