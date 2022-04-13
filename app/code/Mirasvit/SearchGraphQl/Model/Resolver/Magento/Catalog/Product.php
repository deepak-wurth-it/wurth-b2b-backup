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


declare(strict_types=1);

namespace Mirasvit\SearchGraphQl\Model\Resolver\Magento\Catalog;

use Magento\CatalogGraphQl\Model\AttributesJoiner;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\Search;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Mirasvit\Search\Model\Index\Context as IndexContext;

class Product implements ResolverInterface
{
    private $attributesJoiner;

    private $productSearch;

    private $layerResolver;

    private $indexContext;

    private $defaultParams = [
            'sort' => ['relevance' => 'DESC'],
            'filter' => []
        ];

    public function __construct(
        AttributesJoiner $attributesJoiner,
        Search $productSearch,
        LayerResolver $layerResolver,
        IndexContext $indexContext
    ) {
        $this->attributesJoiner = $attributesJoiner;
        $this->productSearch = $productSearch;
        $this->layerResolver = $layerResolver;
        $this->indexContext = $indexContext;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        foreach ($this->defaultParams as $parameter => $defaultValue) {
            if (!isset($args[$parameter])) {
                $args[$parameter] = $defaultValue;
            }
        }

        $layer = $this->layerResolver->get();
        $collection = $layer->getProductCollection();
        $searcher = $this->indexContext->getSearcher();
        $searcher->setInstance($value['instance']);

        if (strpos((string)$collection->getSelect(), '`e`') !== false) {
            $searcher->joinMatches($collection, 'e.entity_id', $args);
        } else {
            $searcher->joinMatches($collection, 'main_table.entity_id', $args);
        }

        $collection->setPageSize($args['pageSize'])
            ->setCurPage($args['currentPage'])
            ->setOrder($args['sort']);

        $items = [];

        foreach ($collection as $product) {
            $productData = $product->getData();
            $productData['model'] = $product;
            $items[] = $productData;
        }

        return $items;
    }
}
