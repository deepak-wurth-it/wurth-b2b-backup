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

namespace Mirasvit\LayeredNavigation\Model\Layer\Filter\CategoryFilter;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Data\Collection\AbstractDb;

class TreeBuilder
{
    public function getItems(CategoryInterface $category, array $facetData): array
    {
        try {
            $items = $this->getDeepCategoryItems($category, $facetData);
        } catch (\Exception $e) {
            return [];
        }

        $entryLevel = $this->getEntryLevel($items);

        $items = $this->sortFilterItems($items, 0, $entryLevel);

        foreach (array_keys($items) as $key) {
            $items[$key]['level'] -= $entryLevel;
        }

        $result = [];
        foreach ($items as $item) {
            if ($item['children'] !== 0) {
                $result[] = $item;
            }
        }

        return $result;
    }

    private function getDeepCategoryItems(CategoryInterface $primaryCategory, array $facetData): array
    {
        $collection = $this->getDeepCategoryCollection($primaryCategory);
        $items      = [];

        foreach ($collection as $category) {
            $count = isset($facetData[$category->getId()])
                ? $facetData[$category->getId()]['count']
                : 0;

            $items[$category->getId()] = [
                'label'       => $category->getName(),
                'value'       => $category->getId(),
                'count'       => $count,
                'level'       => (int)$category->getLevel(),
                'category_id' => (int)$category->getId(),
                'parent_id'   => (int)$category->getParentId(),
                'parent_ids'  => (array)$category->getParentIds(),
                'children'    => $count,
            ];
        }

        foreach ($items as $item) {
            foreach ($item['parent_ids'] as $parentId) {
                if (!isset($items[$parentId])) {
                    continue;
                }

                $items[$parentId]['children'] += $item['count'];
            }
        }

        return array_values($items);
    }

    private function getDeepCategoryCollection(CategoryInterface $parentCategory): AbstractDb
    {
        $collection = $parentCategory->getCollection();

        $collection->addAttributeToSelect('name');

        if ($collection instanceof \Magento\Catalog\Model\ResourceModel\Category\Flat\Collection) {
            $tableAlias = 'main_table';

            $collection
                ->addAttributeToFilter('main_table.is_active', 1)
                ->addFieldToFilter('main_table.path', ['like' => $parentCategory->getPath() . '%'])
                ->addFieldToFilter('main_table.level', ['gt' => $parentCategory->getLevel()])
                ->setOrder('main_table.position', 'asc');
        } else {
            $tableAlias = 'e';

            $collection
                ->addAttributeToFilter('is_active', 1)
                ->addFieldToFilter('path', ['like' => $parentCategory->getPath() . '%'])
                ->addFieldToFilter('level', ['gt' => $parentCategory->getLevel()])
                ->setOrder('position', 'asc');
        }

        $collection->getSelect()->joinLeft(
            ['parent' => $collection->getMainTable()],
            $tableAlias . '.parent_id = parent.entity_id',
            ['parent_path' => 'parent.path']
        );

        return $collection;
    }

    private function sortFilterItems(array $items, int $parentId, int $level): array
    {
        $result = [];

        foreach ($items as $item) {
            $itemId       = $item['category_id'];
            $itemParentId = $item['parent_id'];

            $itemLevel = $item['level'];

            if ($itemLevel !== $level) {
                continue;
            }

            if ($itemParentId != $parentId && $parentId !== 0) {
                continue;
            }

            $subItems = $this->sortFilterItems($items, $itemId, $level + 1);

            if (count($subItems)) {
                $item['is_parent'] = true;
            }

            $result[] = $item;

            foreach ($subItems as $subItem) {
                $result[] = $subItem;
            }
        }

        return $result;
    }

    private function getEntryLevel(array $items): int
    {
        $level = null;

        foreach ($items as $item) {
            if ($level === null) {
                $level = $item['level'];
            }

            $level = min($level, $item['level']);
        }

        return $level ? $level : 0;
    }
}
