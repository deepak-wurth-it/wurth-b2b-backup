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

use Magento\Ui\Component\Form\Field;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Mirasvit\Brand\Api\Data\BrandPageStoreInterface;

class StoreField extends Field
{
    /**
     * @var StoreCheck
     */
    private $storeCheck;

    /**
     * StoreField constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param StoreCheck $storeCheck
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreCheck $storeCheck,
        array $components = [],
        array $data = []
    ) {
        $this->storeCheck =  $storeCheck;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }


    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if ($this->storeCheck->isAppliedAllStores()
            && isset($dataSource['data'][BrandPageStoreInterface::STORE_ID])) {
                unset($dataSource['data'][BrandPageStoreInterface::STORE_ID]);
        }

        return $dataSource;
    }
}
