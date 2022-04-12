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



namespace Mirasvit\Search\Index;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Mirasvit\Search\Api\Data\Index\InstantProviderInterface;
use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Service\IndexService;

abstract class AbstractInstantProvider implements InstantProviderInterface
{
    protected $index;

    private   $collection;

    private   $indexService;

    public function __construct(
        IndexService $indexService
    ) {
        $this->indexService = $indexService;
    }

    public function setIndex(IndexInterface $index): InstantProviderInterface
    {
        $this->index = $index;

        return $this;
    }

    /**
     * @return \Magento\Framework\Data\Collection|AbstractCollection
     */
    public function getCollection(int $limit)
    {
        $collection = $this->indexService->getSearchCollection($this->index);
        $collection->setPageSize($limit);

        if (method_exists($collection, 'getSelect')) {
            if (strpos($collection->getSelect()->__toString(), 'search_result') !== false) {
                $collection->getSelect()->order('score desc');
            }
        }

        return $collection;
    }
}
