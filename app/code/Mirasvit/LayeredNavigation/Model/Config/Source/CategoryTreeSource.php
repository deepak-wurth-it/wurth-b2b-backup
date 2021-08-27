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

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\Option\ArrayInterface;

class CategoryTreeSource implements ArrayInterface
{
    private $categoryFactory;

    /**
     * CategoryTreeSource constructor.
     * @param CategoryFactory $categoryFactory
     */
    public function __construct(
        CategoryFactory $categoryFactory
    ) {
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $root = $this->categoryFactory->create()->load(Category::TREE_ROOT_ID);

        $options = $this->buildRecursive($root);

        return $options;
    }

    /**
     * @param Category $category
     * @return array
     */
    private function buildRecursive(Category $category)
    {
        $tree = [];

        if ($category->getLevel() > 0 && $category->getName()) {
            $tree[] = [
                'value' => $category->getId(),
                'label' => str_repeat('· · ', $category->getLevel() - 1) . $category->getName(),
            ];
        }

        if ($category->hasChildren()) {
            foreach ($category->getChildrenCategories() as $child) {
                foreach ($this->buildRecursive($child) as $item) {
                    $tree[] = $item;
                }
            }
        }

        return $tree;
    }
}
