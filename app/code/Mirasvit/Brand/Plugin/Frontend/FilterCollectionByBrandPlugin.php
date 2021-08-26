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

namespace Mirasvit\Brand\Plugin\Frontend;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Mirasvit\Brand\Registry;
use Mirasvit\Brand\Service\BrandActionService;

/**
 * @see \Magento\Catalog\Model\Layer\CollectionFilterInterface::filter()
 */
class FilterCollectionByBrandPlugin
{
    private $brandActionService;

    private $registry;

    public function __construct(
        BrandActionService $brandActionService,
        Registry $registry
    ) {
        $this->brandActionService = $brandActionService;
        $this->registry           = $registry;
    }

    /**
     * @param object          $subject
     * @param callable        $proceed
     * @param Collection|null $collection
     * @param mixed           ...$args
     */
    public function aroundFilter(object $subject, callable $proceed, Collection $collection = null, ...$args): void
    {
        $proceed($collection, ...$args);

        if (!$this->brandActionService->isBrandViewPage()) {
            return;
        }

        // for brand page we register the root category ID, so products' request_paths are empty
        // to fix this we set flag and add URL-rewrite on category 0
        $collection->setFlag('do_not_use_category_id', true);
        $collection->addUrlRewrite(0);

        $collection->addFieldToFilter(
            $this->registry->getBrand()->getAttributeCode(),
            $this->registry->getBrandPage()->getAttributeOptionId()
        );
    }
}
