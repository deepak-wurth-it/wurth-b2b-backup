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

namespace Mirasvit\Brand\Plugin\Frontend\Magento\Catalog\Model\Layer\Category\FilterableAttributeList;

use Magento\Catalog\Model\Layer\Category\FilterableAttributeList;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as ResourceEavAttribute;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection as AttributeCollection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Brand\Model\Config\Config;
use Mirasvit\Brand\Service\BrandActionService;

class ExcludeCurrentBrandAttributePlugin
{
    private $brandActionService;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        BrandActionService $brandActionService,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        Config $config
    ) {
        $this->brandActionService = $brandActionService;
        $this->collectionFactory  = $collectionFactory;
        $this->storeManager       = $storeManager;
        $this->config             = $config;
    }

    /**
     * Filter product collection
     *
     * @param FilterableAttributeList   $subject
     * @param array|AttributeCollection $collection
     *
     * @return array|AttributeCollection
     */
    public function afterGetList(FilterableAttributeList $subject, $collection)
    {
        if ($this->brandActionService->isBrandViewPage()
            && ($brandAttribute = $this->config->getGeneralConfig()->getBrandAttribute())
        ) {
            $collection = $this->collectionFactory->create();
            $collection->setItemObjectClass(ResourceEavAttribute::class)
                ->addStoreLabel($this->storeManager->getStore()->getId())
                ->setOrder('position', 'ASC')
                ->addIsFilterableFilter()
                ->addFieldToFilter('attribute_code', ['neq' => $brandAttribute]);
            $collection->load();
        }

        return $collection;
    }
}
