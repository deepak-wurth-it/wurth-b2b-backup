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

use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Escaper;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\SeoFilter\Service\RewriteService;

class AttributeFieldset extends Fieldset
{
    private $storeManager;

    private $rewriteService;

    /** @var Attribute */
    private $attribute;

    public function __construct(
        RewriteService $rewriteService,
        StoreManagerInterface $storeManager,
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        array $data = []
    ) {
        $this->rewriteService = $rewriteService;
        $this->storeManager   = $storeManager;
        $this->attribute      = $data[Attribute::class];

        parent::__construct($factoryElement, $factoryCollection, $escaper, [
            'legend' => __('Attribute'),
        ]);
    }

    public function getBasicChildrenHtml(): string
    {
        if (!$this->attribute->getId()) {
            return '';
        }

        foreach ($this->storeManager->getStores() as $store) {
            $id = (int)$store->getId();

            $rewrite = $this->rewriteService->getAttributeRewrite(
                (string)$this->attribute->getAttributeCode(),
                $id
            );

            if (!$rewrite) {
                continue;
            }

            $this->addField('attribute[' . $id . ']', 'text', [
                'name'        => 'attribute[' . $id . ']',
                'label'       => __('URL Alias'),
                'value'       => $rewrite->getRewrite(),
                'placeholder' => $this->attribute->getAttributeCode(),
                'scope_label' => '[' . $store->getCode() . ']',
            ]);
        }

        return (string)parent::getBasicChildrenHtml();
    }
}
