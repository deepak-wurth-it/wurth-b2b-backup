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

namespace Mirasvit\SeoNavigation\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class MetaRobots implements OptionSourceInterface
{
    const EMPTY            = '';
    const NOINDEX_NOFOLLOW = 'noindex-nofollow';
    const NOINDEX_FOLLOW   = 'noindex-follow';
    const INDEX_NOFOLLOW   = 'index-nofollow';
    const INDEX_FOLLOW     = 'index-follow';

    public function toOptionArray(): array
    {
        return [
            ['value' => self::EMPTY, 'label' => (string)__('Don\'t change')],
            ['value' => self::NOINDEX_NOFOLLOW, 'label' => 'NOINDEX, NOFOLLOW'],
            ['value' => self::NOINDEX_FOLLOW, 'label' => 'NOINDEX, FOLLOW'],
            ['value' => self::INDEX_NOFOLLOW, 'label' => 'INDEX, NOFOLLOW'],
            ['value' => self::INDEX_FOLLOW, 'label' => 'INDEX, FOLLOW'],
        ];
    }
}
