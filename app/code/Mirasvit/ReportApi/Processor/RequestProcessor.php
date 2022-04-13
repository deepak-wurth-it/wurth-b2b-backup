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
 * @package   mirasvit/module-report-api
 * @version   1.0.49
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportApi\Processor;

use Mirasvit\ReportApi\Api\RequestInterface;
use Mirasvit\ReportApi\Config\Schema;
use Mirasvit\ReportApi\Handler\CollectionFactory;
use Mirasvit\ReportApi\Service\StoreResolver;
use Psr\Log\LoggerInterface;

class RequestProcessor
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Schema
     */
    private $schema;

    /**
     * @var ResponseBuilder
     */
    private $responseBuilder;

    /**
     * @var StoreResolver
     */
    private $storeResolver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * RequestProcessor constructor.
     * @param CollectionFactory $collectionFactory
     * @param Schema $schema
     * @param ResponseBuilder $responseBuilder
     * @param StoreResolver $storeResolver
     * @param LoggerInterface $logger
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Schema $schema,
        ResponseBuilder $responseBuilder,
        StoreResolver $storeResolver,
        LoggerInterface $logger
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->schema            = $schema;
        $this->responseBuilder   = $responseBuilder;
        $this->storeResolver     = $storeResolver;
        $this->logger            = $logger;
    }

    /**
     * @param RequestInterface $request
     * @return \Mirasvit\ReportApi\Api\ResponseInterface
     */
    public function process(RequestInterface $request)
    {
        $timeStart = microtime(true);

        $collections = $this->assembleCollections($request);

        $query = [];
        foreach ($collections as $collection) {
            $query[] = $collection->__toString();
        }
        $request->setQuery(PHP_EOL . implode(PHP_EOL, $query));

        $this->storeResolver->registerRequest($request);

        $response = $this->responseBuilder->create($request, $collections);

        $this->logger->info('ReportApi', [
            'time'    => microtime(true) - $timeStart,
            'request' => $request->toArray(),
        ]);

        return $response;
    }

    /**
     * @param RequestInterface $request
     * @return \Mirasvit\ReportApi\Handler\Collection[]
     */
    private function assembleCollections(RequestInterface $request)
    {
        /** @var \Mirasvit\ReportApi\Handler\Collection[] $collections */
        $collections = [];

        foreach ($request->getFilters() as $filter) {
            if ($filter->getGroup()) {
                $collections[$filter->getGroup()] = $this->collectionFactory->create();
            }
        }

        if (!$collections) {
            $collections['A'] = $this->collectionFactory->create();
        }

        foreach ($collections as $group => $collection) {
            $groupRequest = clone $request;

            # add filters only for current group or without group
            $filters = [];
            foreach ($groupRequest->getFilters() as $filter) {
                if (!$filter->getGroup() || $filter->getGroup() == $group) {
                    $filters[] = $filter;
                }
            }
            $groupRequest->setFilters($filters);

            $collection->setRequest($groupRequest);
        }

        return $collections;
    }
}
