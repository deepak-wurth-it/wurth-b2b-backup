<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Plazathemes\Override\Model\Category;

class DataProvider extends \Magento\Catalog\Model\Category\DataProvider
{
    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $category = $this->getCurrentCategory();
        if ($category) {
            $categoryData = $category->getData();
            $categoryData = $this->addUseDefaultSettings($category, $categoryData);
            $categoryData = $this->addUseConfigSettings($categoryData);
            $categoryData = $this->filterFields($categoryData);
            if (isset($categoryData['image'])) {
                unset($categoryData['image']);
                $categoryData['image'][0]['name'] = $category->getData('image');
                $categoryData['image'][0]['url'] = $category->getImageUrl2('image');
            }
			
			if (isset($categoryData['thumb_nail'])) {
                unset($categoryData['thumb_nail']);
                $categoryData['thumb_nail'][0]['name'] = $category->getData('thumb_nail');
                $categoryData['thumb_nail'][0]['url'] = $category->getImageUrl2('thumb_nail');
            }
			
			if (isset($categoryData['thumb_popular'])) {
                unset($categoryData['thumb_popular']);
                $categoryData['thumb_popular'][0]['name'] = $category->getData('thumb_popular');
                $categoryData['thumb_popular'][0]['url'] = $category->getImageUrl2('thumb_popular');
            }
			
			if (isset($categoryData['categorytab_image'])) {
                unset($categoryData['categorytab_image']);
                $categoryData['categorytab_image'][0]['name'] = $category->getData('categorytab_image');
                $categoryData['categorytab_image'][0]['url'] = $category->getImageUrl2('categorytab_image');
            }
            $this->loadedData[$category->getId()] = $categoryData;
        }
        return $this->loadedData;
    }

    /**
     * @return array
     */
    protected function getFieldsMap()
    {
        return [
            'general' =>
                [
                    'parent',
                    'path',
                    'is_active',
                    'include_in_menu',
                    'name',
                ],
            'content' =>
                [
                    'image',
                    'thumb_nail',
                    'thumb_popular',
                    'description',
                    'landing_page',
                ],
            'display_settings' =>
                [
                    'display_mode',
                    'is_anchor',
                    'available_sort_by',
                    'use_config.available_sort_by',
                    'default_sort_by',
                    'use_config.default_sort_by',
                    'filter_price_range',
                    'use_config.filter_price_range',
                ],
            'search_engine_optimization' =>
                [
                    'url_key',
                    'url_key_create_redirect',
                    'use_default.url_key',
                    'url_key_group',
                    'meta_title',
                    'meta_keywords',
                    'meta_description',
                ],
            'assign_products' =>
                [
                ],
            'design' =>
                [
                    'custom_use_parent_settings',
                    'custom_apply_to_products',
                    'custom_design',
                    'page_layout',
                    'custom_layout_update',
                ],
            'schedule_design_update' =>
                [
                    'custom_design_from',
                    'custom_design_to',
                ],
            'category_view_optimization' =>
                [
                ],
            'category_permissions' =>
                [
                ],
        ];
    }
}
