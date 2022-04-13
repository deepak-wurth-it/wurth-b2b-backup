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

namespace Mirasvit\Search\Index\External\Wordpress\Post;

use Magento\Framework\Data\Collection;
use Mirasvit\Search\Index\External\Wordpress\Post\CollectionFactory as PostCollectionFactory;
use Mirasvit\Search\Model\Index\AbstractIndex;
use Mirasvit\Search\Model\Index\Context;

class Index extends AbstractIndex
{
    private $postCollectionFactory;

    public function __construct(
        PostCollectionFactory $postCollectionFactory,
        Context $context
    ) {
        $this->postCollectionFactory = $postCollectionFactory;

        parent::__construct($context);
    }

    public function getName(): string
    {
        return 'External / Wordpress Blog';
    }

    public function getIdentifier(): string
    {
        return 'external_wordpress_post';
    }

    public function getAttributeId(string $attributeCode): ?int
    {
        $attributes = array_keys($this->getAttributes());

        return array_search($attributeCode, $attributes);
    }


    public function getAttributes(): array
    {
        return [
            'post_title'   => __('Post Title'),
            'post_content' => __('Post Content'),
            'post_excerpt' => __('Post Excerpt'),
        ];
    }

    public function getPrimaryKey(): string
    {
        return 'ID';
    }

    public function buildSearchCollection(): Collection
    {
        $collection = $this->postCollectionFactory->create(['index' => $this]);

        $this->context->getSearcher()->joinMatches($collection, 'ID');

        return $collection;
    }

    public function getIndexableDocuments(int $storeId, array $entityIds = [], int $lastEntityId = 0, int $limit = 100): array
    {
        $collection = $this->postCollectionFactory->create(['index' => $this]);

        if ($entityIds) {
            $collection->addFieldToFilter('ID', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('ID', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('ID');

        return $collection->toArray()['items'];
    }

    /**
     * Return new connection to wordpress database
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function getConnection()
    {
        if ($this->getIndex()->getProperty('db_connection_name')) {
            $connectionName = $this->getIndex()->getProperty('db_connection_name');

            return $this->context->getResourceConnection()->getConnection($connectionName);
        }

        return $this->context->getResourceConnection()->getConnection();
    }
}
