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

namespace Mirasvit\SearchAutocomplete\Model;

use Magento\Framework\ObjectManagerInterface;
use Mirasvit\Search\Api\Data\Index\InstantProviderInterface;
use Mirasvit\Search\Repository\IndexRepository;

class IndexProvider
{
    private $indexRepository;

    private $configProvider;

    private $objectManager;

    private $providers = [];

    public function __construct(
        IndexRepository $indexRepository,
        ConfigProvider $configProvider,
        ObjectManagerInterface $objectManager,
        array $providers = []
    ) {
        $this->indexRepository = $indexRepository;
        $this->configProvider  = $configProvider;
        $this->objectManager   = $objectManager;
        $this->providers       = $providers;

    }

    public function getIndex(string $identifier): ?Index
    {
        foreach ($this->getList() as $index) {
            if ($index->getIdentifier() === $identifier) {
                return $index;
            }
        }

        return null;
    }

    /**
     * @return Index[]
     */
    public function getList(): array
    {
        $indexes = [];

        foreach ($this->indexRepository->getCollection() as $item) {
            if (!$item->getIsActive()) {
                continue;
            }

            $identifier = $item->getIdentifier();

            $index = new Index();
            $index->setIdentifier($identifier)
                ->setIndexId((int)$item->getIndexId())
                ->setTitle($item->getTitle())
                ->setIsActive(
                    (bool)$this->configProvider->getIndexOptionValue($identifier, Index::IS_ACTIVE, '1')
                )
                ->setPosition(
                    $this->configProvider->getIndexOptionValue($identifier, Index::POSITION)
                        ? (int)$this->configProvider->getIndexOptionValue($identifier, Index::POSITION)
                        : $item->getPosition()
                )
                ->setLimit(
                    (int)$this->configProvider->getIndexOptionValue($identifier, Index::LIMIT, '5')
                );

            $indexes[] = $index;
        }

        usort($indexes, function ($a, $b) {
            return (int)$a->getPosition() - (int)$b->getPosition();
        });

        return $indexes;
    }

    public function getInstantProvider(Index $index): ?InstantProviderInterface
    {
        if (!isset($this->providers[$index->getIdentifier()])) {
            return null;
        }

        /** @var InstantProviderInterface $provider */
        $provider = $this->objectManager->get($this->providers[$index->getIdentifier()]);
        $provider->setIndex(
            $this->indexRepository->get($index->getIndexId())
        );

        return $provider;
    }
}
