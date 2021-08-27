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

namespace Mirasvit\Brand\Model\Brand\PostData;

use Magento\Framework\Filter\FilterManager;
use Mirasvit\Brand\Api\Data\PostData\ProcessorInterface;
use Mirasvit\Brand\Repository\BrandRepository;

class UrlKeyProcessor implements ProcessorInterface
{
    private $brandRepository;

    private $filter;

    public function __construct(
        FilterManager $filter,
        BrandRepository $brandRepository
    ) {
        $this->brandRepository = $brandRepository;
        $this->filter          = $filter;
    }

    public function preparePostData(array $data): array
    {
        if (isset($data['url_key']) && !$data['url_key']
            && isset($data['attribute_option_id']) && $data['attribute_option_id']
        ) {
            $brand = $this->brandRepository->get((int)$data['attribute_option_id']);

            if (!$brand) {
                return $data;
            }

            $optionLabel = $brand->getLabel();

            $data['url_key'] = $this->filter->translitUrl($optionLabel);
        }

        return $data;
    }
}
