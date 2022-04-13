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



namespace Mirasvit\Search\Ui\Stopword\Listing;

use Magento\Framework\Api\Search\SearchResultInterface;
use Mirasvit\Search\Api\Data\StopwordInterface;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * {@inheritdoc}
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $result = [
            'items'        => [],
            'totalRecords' => $searchResult->getTotalCount(),
        ];

        /** @var StopwordInterface $item */
        foreach ($searchResult->getItems() as $item) {
            $data = [
                StopwordInterface::ID       => $item->getId(),
                StopwordInterface::TERM     => $item->getTerm(),
                StopwordInterface::STORE_ID => $item->getStoreId(),
            ];

            $result['items'][] = $data;
        }

        return $result;
    }
}
