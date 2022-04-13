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

use Mirasvit\ReportApi\Api\Processor\ResponseItemInterface;
use Mirasvit\ReportApi\Api\RequestInterface;
use Mirasvit\ReportApi\Api\ResponseInterface;
use Mirasvit\ReportApi\Config\Schema;

class ResponseBuilder
{
    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var ResponseItemFactory
     */
    private $responseItemFactory;

    /**
     * @var ResponseColumnFactory
     */
    private $responseColumnFactory;

    /**
     * @var Schema
     */
    private $schema;

    /**
     * ResponseBuilder constructor.
     * @param ResponseFactory $responseFactory
     * @param ResponseItemFactory $responseItemFactory
     * @param ResponseColumnFactory $responseColumnFactory
     * @param Schema $schema
     */
    public function __construct(
        ResponseFactory $responseFactory,
        ResponseItemFactory $responseItemFactory,
        ResponseColumnFactory $responseColumnFactory,
        Schema $schema
    ) {
        $this->responseFactory       = $responseFactory;
        $this->responseItemFactory   = $responseItemFactory;
        $this->responseColumnFactory = $responseColumnFactory;
        $this->schema                = $schema;
    }

    /**
     * @param RequestInterface                         $request
     * @param \Mirasvit\ReportApi\Handler\Collection[] $collections
     * @return ResponseInterface
     */
    public function create(RequestInterface $request, array $collections)
    {
        $groups = [];
        foreach (array_keys($collections) as $group) {
            $groups[$group] = [];
        }
        foreach ($collections as $group => $collection) {
            foreach ($collection as $data) {
                $pk = '';

                foreach ($request->getDimensions() as $dimension) {
                    $pk .= $this->getPk($dimension, $data, $groups[$group]);
                }
                $groups[$group][$pk] = $data;
            }
        }

        //        foreach ($groups['A'] as $pk => $data) {
        //            foreach ($groups as $group => $items) {
        //                if ($group != 'A') {
        //                    foreach ($items as $sPk => $itm) {
        //                        if ($pk == $sPk) {
        //                            foreach ($itm as $k => $v) {
        //                                $data["$group|$k"] = $v;
        //                            }
        //                        }
        //                    }
        //                }
        //            }
        //
        //            foreach ($request->getDimensions() as $dimension) {
        //                $value          = $data[$dimension];
        //                $result[$value] = $data;
        //            }
        //
        //            $result[] = $this->responseItemFactory->create(['data' => [
        //                ResponseItem::DATA           => $data,
        //                ResponseItem::FORMATTED_DATA => $this->getFormattedData($data),
        //            ]]);
        //        }

        foreach ($groups['A'] as $key => $data) {
            $itemData = [
                ResponseItem::DATA           => $data,
                ResponseItem::FORMATTED_DATA => $this->getFormattedData($data, true),
            ];

            foreach ($groups as $group => $items) {
                if ($group != 'A') {
                    foreach ($items as $sPk => $itm) {
                        if ($key == $sPk) {
                            foreach ($itm as $k => $v) {
                                $itemData[ResponseItem::DATA]["$group|$k"] = $v;
                            }
                            $fItm = $this->getFormattedData($itm);
                            foreach ($fItm as $k => $v) {
                                $itemData[ResponseItem::FORMATTED_DATA]["$group|$k"] = $v;
                            }
                        }
                    }
                }
            }

            $groups['A'][$key] = $this->responseItemFactory->create(['data' => $itemData]);
        }

        $result = $this->groupByDimensions($request->getDimensions(), $groups['A']);

        $columns = [];
        foreach ($request->getColumns() as $name) {
            $column    = $this->schema->getColumn($name);
            $columns[] = $this->responseColumnFactory->create(['data' => [
                ResponseColumn::NAME  => $name,
                ResponseColumn::LABEL => $column->getLabel(),
                ResponseColumn::TYPE  => $column->getType()->getJsType(),
            ]]);
        }

        $totalsData = $collections['A']->getTotals();
        foreach ($collections as $group => $collection) {
            if ($group == 'A') {
                continue;
            }

            foreach ($collection->getTotals() as $k => $v) {
                $totalsData["$group|$k"] = $v;
            }
        }

        $data = [
            Response::SIZE    => $collections['A']->getSize(),
            Response::TOTALS  => $this->responseItemFactory->create(['data' => [
                ResponseItem::DATA           => $totalsData,
                ResponseItem::FORMATTED_DATA => $this->getFormattedData($totalsData, true),
            ]]),
            Response::ITEMS   => $result,
            Response::COLUMNS => $columns,
            Response::REQUEST => $request,
        ];

        // in some cases when result set contains only 1 row the totals may be empty
        // so we simply put the result items in totals
        if (!$totalsData && $data[Response::SIZE] == 1) {
            $data[Response::TOTALS] = reset($result);
        }

        $response = $this->responseFactory->create(
            ['data' => $data]
        );

        return $response;
    }

    /**
     * @param string[]                $dimensions
     * @param ResponseItemInterface[] $data
     * @param int                     $depth
     * @return array
     */
    private function groupByDimensions($dimensions, $data, $depth = 0)
    {
        if (count($dimensions) == 0) {
            return $data;
        }

        $dimension = $dimensions[$depth];

        $result = [];
        foreach ($data as $item) {
            $value = $item->getData($dimension);

            if (!isset($result[$value])) {
                $result[$value] = $this->responseItemFactory->create(['data' => [
                    ResponseItem::DATA           => [
                        $dimension => $item->getData($dimension),
                    ],
                    ResponseItem::FORMATTED_DATA => [
                        $dimension => $item->getFormattedData($dimension),
                    ],
                ]]);
            }

            if ($depth > 0) {
                $item->unsetData($dimensions[$depth - 1]);
            }

            $result[$value]->addItem($item);
        }

        if ($depth + 1 < count($dimensions)) {
            foreach ($result as $d => $data) {
                $result[$d]->setItems(
                    $this->groupByDimensions($dimensions, $data->getItems(), $depth + 1)
                );
            }
        } else {
            foreach ($result as $d => $data) {
                $result[$d] = $result[$d]->getItems()[0];
            }
        }

        return array_values($result);
    }

    /**
     * @param mixed $dimension
     * @param array $data
     * @param array $items
     * @return string
     * @throws \Exception
     */
    private function getPk($dimension, $data, $items)
    {
        $dimensionColumn = $this->schema->getColumn($dimension);

        if (isset($data[$dimension])) {
            $pk = $dimensionColumn->getType()->getPk($data[$dimension], $dimensionColumn->getAggregator());
        } else {
            $pk = 0;
        }

        $idx = 0;
        while (isset($items["{$pk}_{$idx}"])) {
            $idx++;
        }

        return "{$pk}_{$idx}";
    }

    /**
     * @param array $data
     * @param bool $isTotals
     * @return array
     * @throws \Exception
     */
    private function getFormattedData($data, $isTotals = false)
    {
        $formattedData = [];
        foreach ($data as $name => $value) {
            $column = $this->schema->getColumn($name);

            $formattedData[$name] = $column->getType()->getFormattedValue($value, $column->getAggregator());

            if ($isTotals && $value === null) {
                $formattedData[$name] = null;
            }
        }

        return $formattedData;
    }
}
