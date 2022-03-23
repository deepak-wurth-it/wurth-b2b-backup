<?php

namespace Wcb\BestSeller\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Location
 * @package Wcb\BestSeller\Model\Config\SourceF
 */
class Location implements ArrayInterface
{
    const ALLPAGE_CONTENT_TOP = 'allpage.content-top';
    const ALLPAGE_CONTENT_BOTTOM = 'allpage.content-bottom';
    const ALLPAGE_SIDEBAR_TOP = 'allpage.sidebar-top';
    const ALLPAGE_SIDEBAR_BOTTOM = 'allpage.sidebar-bottom';
    const HOMEPAGE_CONTENT_TOP = 'cms_index_index.content-top';
    const HOMEPAGE_CONTENT_BOTTOM = 'cms_index_index.content-bottom';
    const HOMEPAGE_FOOTER_TOP = 'cms_index_index.footer-top';
    const CMS_HOMEPAGE_CONTENT_TOP = 'wuerth_home_index.content-top';
    const CMS_HOMEPAGE_CONTENT_BOTTOM = 'wuerth_home_index.content-bottom';
    const CMS_HOMEPAGE_FOOTER_TOP = 'wuerth_home_index.footer-top';
    const CMS_HOMEPAGE_CUSTOM = 'wuerth_home_index.custom-position';
    const CATEGORY_CONTENT_TOP = 'catalog_category_view.content-top';
    const CATEGORY_CONTENT_BOTTOM = 'catalog_category_view.content-bottom';
    const CATEGORY_SIDEBAR_TOP = 'catalog_category_view.sidebar-top';
    const CATEGORY_SIDEBAR_BOTTOM = 'catalog_category_view.sidebar-bottom';
    const PRODUCT_CONTENT_TOP = 'catalog_product_view.content-top';
    const PRODUCT_CONTENT_BOTTOM = 'catalog_product_view.content-bottom';
    const CHECKOUT_CONTENT_TOP = 'checkout_cart_index.content-top';
    const CHECKOUT_CONTENT_BOTTOM = 'checkout_cart_index.content-bottom';

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = [
            [
                'label' => __('All Page'),
                'value' => [
                    [
                        'label' => __('Top of content'),
                        'value' => self::ALLPAGE_CONTENT_TOP
                    ],
                    [
                        'label' => __('Bottom of content'),
                        'value' => self::ALLPAGE_CONTENT_BOTTOM
                    ],
                    [
                        'label' => __('Top of sidebar'),
                        'value' => self::ALLPAGE_SIDEBAR_TOP
                    ],
                    [
                        'label' => __('Bottom of sidebar'),
                        'value' => self::ALLPAGE_SIDEBAR_BOTTOM
                    ]
                ]
            ],
            [
                'label' => __('Home Page'),
                'value' => [
                    [
                        'label' => __('Top of content'),
                        'value' => self::HOMEPAGE_CONTENT_TOP
                    ],
                    [
                        'label' => __('Bottom of content'),
                        'value' => self::HOMEPAGE_CONTENT_BOTTOM
                    ],
                    [
                        'label' => __('Top of footer'),
                        'value' => self::HOMEPAGE_FOOTER_TOP
                    ]
                ]
            ],
            [
                'label' => __('CMS Home Page'),
                'value' => [
                    [
                        'label' => __('Top of content'),
                        'value' => self::CMS_HOMEPAGE_CONTENT_TOP
                    ],
                    [
                        'label' => __('Bottom of content'),
                        'value' => self::CMS_HOMEPAGE_CONTENT_BOTTOM
                    ],
                    [
                        'label' => __('Top of footer'),
                        'value' => self::CMS_HOMEPAGE_FOOTER_TOP
                    ],
                    [
                        'label' => __('Custom Position'),
                        'value' => self::CMS_HOMEPAGE_CUSTOM
                    ]
                ]
            ],
            [
                'label' => __('Category page'),
                'value' => [
                    [
                        'label' => __('Top of content'),
                        'value' => self::CATEGORY_CONTENT_TOP
                    ],
                    [
                        'label' => __('Bottom of content'),
                        'value' => self::CATEGORY_CONTENT_BOTTOM
                    ],
                    [
                        'label' => __('Top of sidebar'),
                        'value' => self::CATEGORY_SIDEBAR_TOP
                    ],
                    [
                        'label' => __('Bottom of sidebar'),
                        'value' => self::CATEGORY_SIDEBAR_BOTTOM
                    ],
                ]
            ],
            [
                'label' => __('Product page'),
                'value' => [
                    [
                        'label' => __('Top of content'),
                        'value' => self::PRODUCT_CONTENT_TOP
                    ],
                    [
                        'label' => __('Bottom of content'),
                        'value' => self::PRODUCT_CONTENT_BOTTOM
                    ]
                ]
            ],
            [
                'label' => __('Shopping Cart Page'),
                'value' => [
                    [
                        'label' => __('Top of content'),
                        'value' => self::CHECKOUT_CONTENT_TOP
                    ],
                    [
                        'label' => __('Bottom of content'),
                        'value' => self::CHECKOUT_CONTENT_BOTTOM
                    ]
                ]
            ]
        ];

        return $options;
    }
}
