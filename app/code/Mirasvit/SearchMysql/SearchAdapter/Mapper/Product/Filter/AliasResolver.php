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
 * @package   mirasvit/module-search-ultimate
 * @version   2.0.56
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\Filter;

use Magento\CatalogSearch\Model\Search\RequestGenerator;
use Magento\Framework\Search\Request\FilterInterface;

class AliasResolver
{
    /**
     * The suffix for stock status filter that may be added to the query beside the filter query
     * Used when showing of Out of Stock products is disabled.
     */
    const STOCK_FILTER_SUFFIX = '_stock';

    /**
     * @since 100.1.6
     */
    public function getAlias(FilterInterface $filter): string
    {
        $alias = null;
        $field = $filter->getField();
        switch ($field) {
            case 'price':
                $alias = 'price_index';
                break;
            case 'category_ids':
                $alias = 'category_ids_index';
                break;
            default:
                $alias = $field . RequestGenerator::FILTER_SUFFIX;
                break;
        }
        return $alias;
    }
}
