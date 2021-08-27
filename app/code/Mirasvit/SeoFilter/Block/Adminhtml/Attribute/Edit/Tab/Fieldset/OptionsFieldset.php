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
 * @package   mirasvit/module-seo-filter
 * @version   1.1.5
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\SeoFilter\Block\Adminhtml\Attribute\Edit\Tab\Fieldset;

use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Widget\Context;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Mirasvit\SeoFilter\Service\RewriteService;

/**
 * @SuppressWarnings(PHPMD)
 */
class OptionsFieldset extends Widget
{
    private $rewriteService;

    private $attribute;

    private $formFactory;

    private $eavConfig;

    public function __construct(
        RewriteService $rewriteService,
        FormFactory $formFactory,
        Context $context,
        Config $eavConfig,
        Registry $registry
    ) {
        $this->rewriteService = $rewriteService;
        $this->formFactory    = $formFactory;
        $this->eavConfig      = $eavConfig;

        $this->attribute = $registry->registry('entity_attribute');

        parent::__construct($context);
    }

    public function getStores(): array
    {
        $stores = [];

        foreach ($this->_storeManager->getStores() as $store) {
            $stores[$store->getId()] = $store->getCode();
        }

        return $stores;
    }

    public function getOptions(): array
    {
        $attribute = $this->getAttribute();

        $options = [];

        foreach ($attribute->getSource()->getAllOptions() as $option) {
            if (isset($option['value']) && $option['value']) {
                $optionId = (int)$option['value'];
                $name     = (string)$option['label'];

                $option = [
                    'id'      => $optionId,
                    'value'   => $optionId,
                    'name'    => $name,
                    'rewrite' => [],
                ];

                foreach ($this->_storeManager->getStores() as $store) {
                    $storeId = (int)$store->getId();

                    $rewrite = $this->rewriteService->getOptionRewrite(
                        (string)$attribute->getAttributeCode(),
                        (string)$optionId,
                        $storeId
                    );

                    $option['rewrite'][(int)$store->getId()] = $rewrite->getRewrite();
                }

                $options[] = $option;
            }
        }


        return $options;
    }


    protected function _construct(): void
    {
        parent::_construct();

        $this->setTemplate('Mirasvit_SeoFilter::product/attribute/tab/fieldset/options.phtml');
    }

    private function getAttribute(): AbstractAttribute
    {
        return $this->eavConfig->getAttribute('catalog_product', $this->attribute->getAttributeCode());
    }
}
