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



namespace Mirasvit\Search\Index\External\Wordpress\Post;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Psr\Log\LoggerInterface;

class Collection extends AbstractCollection
{
    /**
     * @var Index
     */
    private $index;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        EventManagerInterface $eventManager,
        ?Index $index = null
    ) {
        $this->index = $index;

        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager);

        $this->setConnection($this->index->getConnection());
        $this->setModel(Item::class);
        $this->_initSelect();
    }

    public function getMainTable(): string
    {
        return $this->index->getIndex()->getProperty('db_table_prefix') . 'posts';
    }

    public function getResourceModelName(): string
    {
        return \Mirasvit\Search\Model\ResourceModel\Index::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $url = $this->index->getIndex()->getProperty('url_template');
        $parts = preg_split('#\/#', $url);

        foreach ($parts as $key => $value) {
            if (!preg_match('#\{.*\}#', $value) || $value == '{post_name}' || $value == '{ID}') {
                unset($parts[$key]);
            }
        }

        if (!empty($parts)) {
            $termRelationships = $this->index->getIndex()->getProperty('db_table_prefix') . 'term_relationships';
            $termTaxonomy = $this->index->getIndex()->getProperty('db_table_prefix') . 'term_taxonomy';
            $terms = $this->index->getIndex()->getProperty('db_table_prefix') . 'terms';

            $this->getSelect()
                ->joinLeft(
                    ['termRelationships' => $termRelationships],
                    'main_table.ID = termRelationships.object_id',
                    []
                )->joinLeft(
                    ['termTaxonomy' => $termTaxonomy],
                    'termRelationships.term_taxonomy_id = termTaxonomy.term_id',
                    []
                );
            foreach ($parts as $part) {
                $part = str_replace(['{', '}'], '', $part);
                $this->getSelect()->joinLeft(
                    [$part .'terms' => $terms],
                    'termTaxonomy.term_id = '. $part .'terms.term_id',
                    [$part => $part .'terms.slug']
                )->where('termTaxonomy.taxonomy = ?', $part);
            }
        }

        $this->getSelect()->where('main_table.post_status=?', 'publish');
        $this->getSelect()->group('main_table.ID');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        /** @var Item $item */
        foreach ($this->_items as $item) {
            $item->setInstance($this->index);
        }

        return parent::_afterLoad();
    }
}
