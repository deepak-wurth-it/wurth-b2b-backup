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

namespace Mirasvit\LayeredNavigation\Plugin;

use Mirasvit\LayeredNavigation\Model\DataMapper;

class PutExtraFiltersToIndexPlugin
{
    private $newDataMapper;

    private $onSaleDataMapper;

    private $ratingDataMapper;

    private $stockDataMapper;

    public function __construct(
        DataMapper\NewDataMapper $newDataMapper,
        DataMapper\OnSaleDataMapper $onSaleDataMapper,
        DataMapper\RatingDataMapper $ratingDataMapper,
        DataMapper\StockDataMapper $stockDataMapper
    ) {
        $this->newDataMapper    = $newDataMapper;
        $this->onSaleDataMapper = $onSaleDataMapper;
        $this->ratingDataMapper = $ratingDataMapper;
        $this->stockDataMapper  = $stockDataMapper;
    }

    /**
     * @param object $subject
     * @param array  $documents
     * @param int    $storeId
     * @param int    $mappedIndexerId
     *
     * @return array
     */
    public function beforeAddDocs($subject, array $documents, $storeId, $mappedIndexerId)
    {
        if (isset(array_values($documents)[0]['trigram'])) {
            return [$documents, $storeId, $mappedIndexerId];
        }

        $storeId   = (int)$storeId;
        $documents = $this->newDataMapper->map($documents, $storeId);
        $documents = $this->onSaleDataMapper->map($documents, $storeId);
        $documents = $this->ratingDataMapper->map($documents, $storeId);
        $documents = $this->stockDataMapper->map($documents, $storeId);

        return [$documents, $storeId, $mappedIndexerId];
    }
}
