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

namespace Mirasvit\Brand\Model\ResourceModel\BrandPage;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Option\ArrayInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Brand\Api\Data\BrandPageInterface;
use Mirasvit\Brand\Api\Data\BrandPageStoreInterface;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends AbstractCollection implements ArrayInterface
{
    /**
     * @var string
     */
    protected $_idFieldName = BrandPageInterface::ID; //use in massaction

    private $resource;

    private $connection;

    private $eventManager;

    private $fetchStrategy;

    private $logger;

    private $entityFactory;

    private $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager,
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->storeManager  = $storeManager;
        $this->entityFactory = $entityFactory;
        $this->logger        = $logger;
        $this->fetchStrategy = $fetchStrategy;
        $this->eventManager  = $eventManager;
        $this->connection    = $connection;
        $this->resource      = $resource;

        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Mirasvit\Brand\Model\BrandPage::class,
            \Mirasvit\Brand\Model\ResourceModel\BrandPage::class
        );
    }

    /**
     * Add Filter by store.
     *
     * @param int|\Magento\Store\Model\Store|\Magento\Store\Api\Data\StoreInterface $store
     *
     * @return $this
     */
    public function addStoreFilter($store)
    {
        $this->joinStoreTable();

        if ($store instanceof \Magento\Store\Model\Store) {
            $store = [$store->getId()];
        }

        $this->getSelect()
            ->where('store_table.' . BrandPageStoreInterface::STORE_ID . ' in (?)', [0, $store]);

        return $this;
    }

    /**
     * Add Filter by status.
     *
     * @param int $status
     *
     * @return $this
     */
    public function addEnableFilter($status = 1)
    {
        $this->getSelect()->where('main_table.' . BrandPageInterface::IS_ACTIVE . ' = ?', $status);

        return $this;
    }

    /**
     * @return $this
     */
    public function addStoreColumn()
    {
        $this->getSelect()
            ->columns(
                ['store_id' => new \Zend_Db_Expr(
                    "(SELECT GROUP_CONCAT(" . BrandPageStoreInterface::STORE_ID
                    . ") FROM `{$this->getTable(BrandPageStoreInterface::TABLE_NAME)}`
                    AS `" . BrandPageStoreInterface::TABLE_NAME . "`
                    WHERE main_table." . BrandPageInterface::ID
                    . " = " . BrandPageStoreInterface::TABLE_NAME
                    . "." . BrandPageStoreInterface::BRAND_PAGE_ID . ")"
                )]
            );

        return $this;
    }

    private function joinStoreTable()
    {
        if ($this->getFlag(BrandPageStoreInterface::TABLE_NAME)) {
            return $this;
        }

        $this->getSelect()
            ->join(
                ['store_table' => $this->getTable(BrandPageStoreInterface::TABLE_NAME)],
                'main_table.' . BrandPageInterface::ID . ' = store_table.' . BrandPageStoreInterface::BRAND_PAGE_ID,
                []
            );

        $this->setFlag(BrandPageStoreInterface::TABLE_NAME, true);

        return $this;
    }
}
