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

namespace Mirasvit\QuickNavigation\Service;

use Magento\Catalog\Model\Layer;
use Mirasvit\QuickNavigation\Api\Data\SequenceInterface;
use Mirasvit\QuickNavigation\Context;
use Mirasvit\QuickNavigation\Model\ConfigProvider;
use Mirasvit\QuickNavigation\Repository\SequenceRepository;

class PredictService
{
    private $configProvider;

    private $filterList;

    private $sequenceRepository;

    private $context;

    public function __construct(
        ConfigProvider $configProvider,
        SequenceRepository $sequenceRepository,
        Context $context,
        Layer\FilterList $filterList
    ) {
        $this->configProvider     = $configProvider;
        $this->filterList         = $filterList;
        $this->sequenceRepository = $sequenceRepository;
        $this->context            = $context;
    }

    /**
     * @SuppressWarnings(PHPMD)
     * @return Layer\Filter\Item[]
     */
    public function getFilterItems(): array
    {
        $filterList = [];
        foreach ($this->getSuitableSequences() as $sequence) {
            $items = $this->splitSequence($sequence->getSequence());

            foreach ($items as $item) {
                $attr  = $item['attribute'];
                $value = $item['value'];

                $key = $attr . ':' . $value;
                if (!isset($filterList[$key])) {
                    $filterList[$key] = 0;
                }

                $filterList[$key] += $sequence->getPopularity();
            }
        }

        uasort($filterList, function ($a, $b) {
            return $a < $b;
        });

        $itemsList    = [];
        $totalCounter = 0;
        foreach ($filterList as $key => $popularity) {
            if ($totalCounter >= $this->configProvider->getTotalThreshold()) {
                break;
            }

            [$attr, $value] = explode(':', $key);

            if (!isset($itemsList[$attr])) {
                $itemsList[$attr] = [
                    'popularity' => 0,
                    'values'     => [],
                ];
            }

            $itemsList[$attr]['popularity'] += $popularity;

            if (count($itemsList[$attr]['values']) < $this->configProvider->getAttributeThreshold()) {
                $itemsList[$attr]['values'][] = $value;

                $totalCounter++;
            }
        }

        uasort($itemsList, function ($a, $b) {
            return $a['popularity'] < $b['popularity'];
        });

        $filterList = [];

        foreach ($this->context->getState()->getFilters() as $filterItem) {
            $filterList[(string)$filterItem->getValueString()] = $filterItem;
        }

        $layerFilters = $this->filterList->getFilters($this->context->getLayer());

        foreach ($itemsList as $attr => $data) {
            foreach ($data['values'] as $value) {
                foreach ($layerFilters as $filter) {
                    /** @var Layer\Filter\Item $filterItem */
                    foreach ($filter->getItems() as $filterItem) {
                        if ($filterItem->getValueString() == $value && $filter->getRequestVar() == $attr) {
                            $filterList[$filterItem->getValueString()] = $filterItem;
                        }
                    }
                }
            }
        }

        return array_values($filterList);
    }

    /**
     * @return SequenceInterface[]|\Mirasvit\QuickNavigation\Model\ResourceModel\Sequence\Collection
     */
    private function getSuitableSequences()
    {
        $sequenceString = $this->context->getSequenceString();
        $sequenceLength = $this->context->getSequenceLength();

        $collection = $this->sequenceRepository->getCollection();
        $collection->addFieldToFilter(SequenceInterface::STORE_ID, $this->context->getStoreId())
            ->addFieldToFilter(SequenceInterface::CATEGORY_ID, $this->context->getCategoryId())
            ->setOrder(SequenceInterface::POPULARITY, 'desc');

        if ($sequenceLength > 0) {
            $sequenceList = explode('|', $sequenceString);

            foreach ($sequenceList as $item) {
                $collection->addFieldToFilter(SequenceInterface::SEQUENCE, [
                    'like' => '%' . $item . '%',
                ]);
            }
        } else {
            $collection->addFieldToFilter(SequenceInterface::LENGTH, 1);
        }

        return $collection;
    }

    private function splitSequence(string $sequence): array
    {
        $items   = explode('|', $sequence);
        $filters = [];
        foreach ($items as $item) {
            if (count(explode(':', $item)) < 2) {
                continue;
            }

            [$code, $value] = explode(':', $item);

            $filters[] = [
                'attribute' => $code,
                'value'     => $value,
            ];
        }

        return $filters;
    }
}
