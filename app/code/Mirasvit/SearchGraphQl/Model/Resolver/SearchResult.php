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

namespace Mirasvit\SearchGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Search\Model\QueryFactory;
use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Repository\IndexRepository;
use Mirasvit\SearchAutocomplete\Model\IndexProvider;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;

class SearchResult implements ResolverInterface
{
    private $indexRepository;

    private $indexProvider;

    private $queryFactory;

    private $layerResolver;

    public function __construct(
        IndexRepository $indexRepository,
        IndexProvider $indexProvider,
        QueryFactory $queryFactory,
        LayerResolver $layerResolver
    ) {
        $this->indexRepository = $indexRepository;
        $this->indexProvider   = $indexProvider;
        $this->queryFactory    = $queryFactory;
        $this->layerResolver   = $layerResolver;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($args['query'])) {
            throw new GraphQlInputException(__('"Query should be specified'));
        }

        $this->queryFactory->get()
            ->setQueryText($args['query'])
            ->setData('is_query_text_short', false);

        $collection = $this->indexRepository->getCollection()
            ->addFieldToFilter(IndexInterface::IS_ACTIVE, 1)
            ->setOrder(IndexInterface::POSITION, 'asc');

        $this->layerResolver->create(LayerResolver::CATALOG_LAYER_SEARCH);
        $indexList = [];

        foreach ($collection as $index) {
            $indexItem = [
                IndexInterface::IDENTIFIER => $index->getIdentifier(),
                IndexInterface::TITLE      => $index->getTitle(),
                IndexInterface::POSITION   => $index->getPosition(),
            ];

            $indexInstance = $this->indexRepository->getInstance($index);
            $indexItem['instance'] = $indexInstance;
            $indexList[$index->getIdentifier()] = $indexItem;
        }

        return $indexList;
    }
}
