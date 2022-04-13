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



namespace Mirasvit\Search\Plugin;

use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Repository\IndexRepository;
use Mirasvit\SearchAutocomplete\InstantProvider\ConfigMaker;

/**
 * @see \Magento\CatalogSearch\Model\Indexer\Fulltext::executeFull()
 */
class FullReindexPlugin
{
    private $indexRepository;

    private $configMaker;

    public function __construct(
        IndexRepository $indexRepository,
        ConfigMaker $configMaker
    ) {
        $this->indexRepository = $indexRepository;
        $this->configMaker     = $configMaker;
    }

    /**
     * @param mixed $scope
     */
    public function aroundExecuteFull(object $fulltext, \Closure $proceed, $scope = null): ?array
    {
        try {
            $this->configMaker->ensure();
        } catch (\Exception $e) {}

        foreach ($this->indexRepository->getCollection() as $index) {
            if ($index->getIsActive()) {
                if ($index->getIdentifier() != 'catalogsearch_fulltext') {
                    $this->indexRepository->getInstance($index)->reindexAll();
                }
            }
        }

        $result = $proceed($scope);
        $index = $this->indexRepository->getCollection()
            ->addFieldToFilter('identifier', ['eq' => 'catalogsearch_fulltext'])->getFirstItem();
        $index->setStatus(IndexInterface::STATUS_READY)->save();

        return $result;
    }
}
