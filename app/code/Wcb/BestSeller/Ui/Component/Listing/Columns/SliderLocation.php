<?php

namespace Wcb\BestSeller\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;
use Wcb\BestSeller\Model\Config\Source\Location;

/**
 * Class CommentContent
 * @package Mageplaza\Blog\Ui\Component\Listing\Columns
 */
class SliderLocation extends Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$this->getData('name')])) {
                    $data = $this->getLocation($item[$this->getData('name')]);
                    $type = $data['type'];
                    $location = $data['location'];

                    $item[$this->getData('name')] = '<b>' . $type . '</b></br>' . '<span>' . $location . '</span>';
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param $data
     *
     * @return array
     */
    public function getLocation($data)
    {
        $location = [];
        switch ($data) {
            case Location::ALLPAGE_CONTENT_TOP:
                $location['type'] = 'All Page';
                $location['location'] = 'Top of Content';
                break;
            case Location::ALLPAGE_CONTENT_BOTTOM:
                $location['type'] = 'All Page';
                $location['location'] = 'Bottom of Content';
                break;
            case Location::ALLPAGE_SIDEBAR_TOP:
                $location['type'] = 'All Page';
                $location['location'] = 'Sidebar Top';
                break;
            case Location::ALLPAGE_SIDEBAR_BOTTOM:
                $location['type'] = 'All Page';
                $location['location'] = 'Sidebar Bottom';
                break;
            case Location::HOMEPAGE_CONTENT_TOP:
                $location['type'] = 'Home Page';
                $location['location'] = 'Top of Content';
                break;
            case Location::HOMEPAGE_CONTENT_BOTTOM:
                $location['type'] = 'Home Page';
                $location['location'] = 'Bottom of Content';
                break;
            case Location::CATEGORY_CONTENT_TOP:
                $location['type'] = 'Category Page';
                $location['location'] = 'Top of Content';
                break;
            case Location::CATEGORY_CONTENT_BOTTOM:
                $location['type'] = 'Category Page';
                $location['location'] = 'Bottom of Content';
                break;
            case Location::CATEGORY_SIDEBAR_TOP:
                $location['type'] = 'Category Page';
                $location['location'] = 'Sidebar Top';
                break;
            case Location::CATEGORY_SIDEBAR_BOTTOM:
                $location['type'] = 'Category Page';
                $location['location'] = 'Sidebar Bottom';
                break;
            case Location::PRODUCT_CONTENT_TOP:
                $location['type'] = 'Product Page';
                $location['location'] = 'Top of Content';
                break;
            case Location::PRODUCT_CONTENT_BOTTOM:
                $location['type'] = 'Product Page';
                $location['location'] = 'Bottom of Content';
                break;
            case Location::CHECKOUT_CONTENT_TOP:
                $location['type'] = 'Checkout Page';
                $location['location'] = 'Top of Content';
                break;
            case Location::CHECKOUT_CONTENT_BOTTOM:
                $location['type'] = 'Checkout Page';
                $location['location'] = 'Bottom of Content';
                break;
            case Location::CMS_HOMEPAGE_CONTENT_TOP:
                $location['type'] = 'CMS Home Page';
                $location['location'] = 'Top of Content';
                break;
            case Location::CMS_HOMEPAGE_CONTENT_BOTTOM:
                $location['type'] = 'CMS Home Page';
                $location['location'] = 'Bottom of Content';
                break;
        }

        return $location;
    }
}
