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
 * @package   mirasvit/module-seo-filter
 * @version   1.1.5
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\SeoFilter\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Mirasvit\SeoFilter\Model\ConfigProvider;

class UrlFormatSource implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            [
                'value' => ConfigProvider::URL_FORMAT_OPTIONS,
                'label' => __('Short: category/option1-option2-option3'),
            ],
            [
                'value' => ConfigProvider::URL_FORMAT_ATTR_OPTIONS,
                'label' => __('Long: category/attr1/option1-option2/attr2/option3'),
            ],
        ];
    }
}
