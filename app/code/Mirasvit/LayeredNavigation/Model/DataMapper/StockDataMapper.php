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

namespace Mirasvit\LayeredNavigation\Model\DataMapper;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Mirasvit\LayeredNavigation\Model\Config\ExtraFilterConfigProvider;

class StockDataMapper
{
    private $stockState;

    private $extraFilterConfigProvider;

    public function __construct(
        ExtraFilterConfigProvider $extraFilterConfigProvider,
        StockRegistryInterface $stockState
    ) {
        $this->stockState                = $stockState;
        $this->extraFilterConfigProvider = $extraFilterConfigProvider;
    }

    public function map(array $documents, int $storeId): array
    {
        if (!$this->extraFilterConfigProvider->isStockFilterEnabled()) {
            return $documents;
        }

        foreach ($documents as $id => $doc) {
            $stockStatus = $this->stockState->getStockStatus($id)->getStockStatus() ? 2 : 1;

            $doc[ExtraFilterConfigProvider::STOCK_FILTER]          = $stockStatus;
            $doc[ExtraFilterConfigProvider::STOCK_FILTER . '_raw'] = $stockStatus;

            $documents[$id] = $doc;
        }

        return $documents;
    }
}
