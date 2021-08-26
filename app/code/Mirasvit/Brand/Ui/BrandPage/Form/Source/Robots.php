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

namespace Mirasvit\Brand\Ui\BrandPage\Form\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Mirasvit\Brand\Model\Config\BrandPageConfig;

class Robots implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        $data = [
            BrandPageConfig::INDEX_FOLLOW     => BrandPageConfig::INDEX_FOLLOW,
            BrandPageConfig::NOINDEX_FOLLOW   => BrandPageConfig::NOINDEX_FOLLOW,
            BrandPageConfig::INDEX_NOFOLLOW   => BrandPageConfig::INDEX_NOFOLLOW,
            BrandPageConfig::NOINDEX_NOFOLLOW => BrandPageConfig::NOINDEX_NOFOLLOW,
        ];

        $options = [];
        foreach ($data as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }

        return $options;
    }
}
