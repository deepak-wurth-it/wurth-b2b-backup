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
namespace Mirasvit\Brand\Ui\BrandPage\Form\Component\Store;

use Mirasvit\Brand\Model\ResourceModel\BrandPage\Collection;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Brand\Api\Data\BrandPageStoreInterface;

class StoreCheck
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var Collection
     */
    private $collection;
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * StoreCheck constructor.
     * @param RequestInterface $request
     * @param Collection $collection
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        RequestInterface $request,
        Collection $collection,
        StoreManagerInterface $storeManager
    ) {
        $this->request = $request;
        $this->collection = $collection;
        $this->storeManager = $storeManager;
    }

    /**
     * Check if applied for all stores
     *
     * @return bool
     */
    public function isAppliedAllStores()
    {
        if ($id = $this->request->getParam('id')) {
            return $this->isAllStoresSelected($id);
        } elseif (($stores = $this->storeManager->getStores())
            && is_array($stores)
            && count($stores) < 2) {
                return true;
        }

        return false;
    }

    /**
     * Check if all stores (store_id = 0)
     *
     * @param int $id
     * @return bool
     */
    protected function isAllStoresSelected($id)
    {
        $item = $this->collection
            ->addStoreColumn()
            ->addFieldToFilter(BrandPageStoreInterface::BRAND_PAGE_ID, $id)
            ->getFirstItem();

        $storeIds = $item->getData(BrandPageStoreInterface::STORE_ID);
        if ((is_array($storeIds) && count($storeIds) == 1
                && isset($storeIds[0]) && $storeIds[0] == 0)
            || ($storeIds == 0)) {
            return true;
        }

        return false;
    }
}
