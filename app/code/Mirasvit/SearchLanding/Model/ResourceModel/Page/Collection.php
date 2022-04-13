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



namespace Mirasvit\SearchLanding\Model\ResourceModel\Page;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\SearchLanding\Model\Page', 'Mirasvit\SearchLanding\Model\ResourceModel\Page');
    }

    public function addStoreFilter(int $storeId): Collection
    {
        $id = intval($storeId);
        $this->getSelect()->where('(FIND_IN_SET (' . $id . ', main_table.store_ids)
            OR FIND_IN_SET (0, main_table.store_ids))');

        return $this;
    }
}
