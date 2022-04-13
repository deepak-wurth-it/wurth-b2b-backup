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



namespace Mirasvit\SearchMysql\SearchAdapter\Aggregation;

use Magento\Framework\Api\Search\Document;
use Magento\Framework\DataObject;
use Magento\Framework\Search\Adapter\Aggregation\AggregationResolverInterface;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;
use Magento\Framework\Search\RequestInterface;

class Builder
{
    private $dataProviderContainer;

    private $aggregationContainer;

    private $aggregationResolver;

    private $temporaryStorage;

    public function __construct(
        DataProviderContainer $dataProviderContainer,
        Builder\Container $aggregationContainer,
        AggregationResolverInterface $aggregationResolver,
        TemporaryStorage $temporaryStorage
    ) {
        $this->dataProviderContainer = $dataProviderContainer;
        $this->aggregationContainer  = $aggregationContainer;
        $this->aggregationResolver   = $aggregationResolver;
        $this->temporaryStorage      = $temporaryStorage;
    }

    public function build(RequestInterface $request, array $documents = []): array
    {
        return $this->processAggregations($request, $documents);
    }

    private function processAggregations(RequestInterface $request, array $documents): array
    {
        $aggregations = [];
        $documentIds  = $this->extractDocumentIds($documents);
        $docs         = [];
        foreach ($documents as $document) {
            $docs[] = (new Document($document))->setId($document['_id'])
                ->setCustomAttribute('score', new DataObject(['value' => $document['_score'],]));
        }
        $documentsTable = $this->temporaryStorage->storeApiDocuments($docs);

        $buckets      = $this->aggregationResolver->resolve($request, $documentIds);
        $dataProvider = $this->dataProviderContainer->get($request->getIndex());
        foreach ($buckets as $bucket) {
            $aggregationBuilder               = $this->aggregationContainer->get($bucket->getType());
            $aggregations[$bucket->getName()] = $aggregationBuilder->build(
                $dataProvider,
                $request->getDimensions(),
                $bucket,
                $documentsTable
            );
        }

        return $aggregations;
    }

    private function extractDocumentIds(array $documents): array
    {
        return $documents ? array_keys($documents) : [];
    }
}
