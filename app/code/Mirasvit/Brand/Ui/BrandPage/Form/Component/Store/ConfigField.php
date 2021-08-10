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

class ConfigField extends Field
{
    /**
     * @var StoreCheck
     */
    private $storeCheck;

    /**
     * ConfigField constructor.
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
     * {@inheritdoc}
     */
    public function setData($key, $value = null)
    {
        if ($key == 'config'
            && isset($value['dataScope'])
            && $value['dataScope'] == 'use_config.stores'
            && $this->storeCheck->isAppliedAllStores()) {
                $value['value'] = true;
        }

        parent::setData($key, $value);
    }
}
