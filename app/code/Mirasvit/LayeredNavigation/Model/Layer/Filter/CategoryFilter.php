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

namespace Mirasvit\LayeredNavigation\Model\Layer\Filter;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Framework\App\RequestInterface;

class CategoryFilter extends AbstractFilter
{
    const ATTRIBUTE = 'category_ids';
    const CATEGORY  = 'category';

    private $layer;

    private $dataProvider;

    private $treeBuilder;

    public function __construct(
        CategoryFilter\TreeBuilder $treeBuilder,
        CategoryFactory $categoryDataProviderFactory,
        LayerResolver $layerResolver,
        Layer $layer,
        Context $context,
        array $data = []
    ) {
        parent::__construct($layer, $context, $data);

        $this->_requestVar  = 'cat';
        $this->treeBuilder  = $treeBuilder;
        $this->dataProvider = $categoryDataProviderFactory->create(['layer' => $this->getLayer()]);
        $this->layer        = $layerResolver->get();
    }

    public function apply(RequestInterface $request): self
    {
        $categoryId = $request->getParam($this->getRequestVar()) ? : $request->getParam('id');

        if (empty($categoryId)) {
            return $this;
        }

        $categoryIds = explode(',', (string)$categoryId);
        $categoryIds = array_unique($categoryIds);
        $categoryIds = array_map('intval', $categoryIds); //must be int
        $categoryIds = array_diff($categoryIds, ['', 0, false, null]); //don't use incorrect data

        if ($request->getParam('id') != $categoryId) {
            $this->getProductCollection()
                ->addFieldToFilter('category_ids', $categoryIds);

            $category = $this->getLayer()->getCurrentCategory();
            /** @var \Magento\Catalog\Model\ResourceModel\AbstractCollection $collection */
            $collection = $category->getCollection();
            $child      = $collection
                ->addFieldToFilter($category->getIdFieldName(), $categoryIds)
                ->addAttributeToSelect('name');
            $this->addState(false, $categoryIds, $child);
        }

        return $this;
    }

    public function getName(): string
    {
        return (string)__('Category');
    }

    /**
     * Add data to state
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @param string|bool                                                  $categoryName
     * @param array<int, int>                                              $categoryId
     * @param bool|\Magento\Catalog\Model\ResourceModel\AbstractCollection $child
     *
     * @return bool
     */
    protected function addState($categoryName, $categoryId, $child = false)
    {

        if (is_array($categoryId) && $child && $this->stateBarConfigProvider->isFilterClearBlockInOneRow()) {
            $labels = [];
            foreach ($categoryId as $categoryIdValue) {
                if ($currentCategory = $child->getItemById($categoryIdValue)) {
                    $labels[] = $currentCategory->getName();
                }
            }
            $this->addStateItem(
                $this->_createItem(
                    implode(', ', $labels),
                    $categoryId
                )
            );
        } elseif (is_array($categoryId) && $child) {
            foreach ($categoryId as $categoryIdValue) {
                if ($currentCategory = $child->getItemById($categoryIdValue)) {
                    $this->addStateItem(
                        $this->_createItem(
                            $currentCategory->getName(),
                            $categoryIdValue
                        )
                    );
                }
            }
        } else {
            $this->addStateItem(
                $this->_createItem(
                    $categoryName,
                    $categoryId
                )
            );
        }


        return true;
    }

    protected function _initItems(): self
    {
        $category = $this->dataProvider->getCategory();

        if (!$category->getIsActive()) {
            return $this;
        }

        $optionsFacetedData = $this->getProductCollection()->getExtendedFacetedData(
            self::CATEGORY,
            $this->configProvider->isMultiselectEnabled(),
            (int)$category->getId()
        );

        $items = $this->treeBuilder->getItems($category, $optionsFacetedData);

        $this->_items = [];
        foreach ($items as $data) {
            if (!$this->configProvider->isShowNestedCategories()) {
                $data['level'] = 0;

                if ($data['count'] === 0) {
                    continue;
                }
            }

            $item = $this->_createItem($data['label'], $data['value'], $data['count']);
            $item->addData($data);

            $this->_items[] = $item;
        }

        return $this;
    }
}
