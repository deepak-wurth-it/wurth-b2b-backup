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

namespace Mirasvit\LayeredNavigation\Block\Adminhtml\Attribute\Edit\Tab\Fieldset;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Escaper;
use Mirasvit\LayeredNavigation\Api\Data\AttributeConfigInterface;
use Mirasvit\LayeredNavigation\Model\Config\Source\AttributeOptionSortBySource;

class MiscFieldset extends Fieldset
{
    private $attributeOptionSortBySource;

    private $request;

    /**
     * @var Attribute
     */
    private $attribute;

    private $attributeConfig;

    public function __construct(
        AttributeOptionSortBySource $attributeOptionSortBySource,
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        RequestInterface $request,
        array $data = []
    ) {

        $this->attributeOptionSortBySource = $attributeOptionSortBySource;
        $this->attributeConfig             = $data[AttributeConfigInterface::class];
        $this->request                     = $request;
        $this->attribute                   = $data[Attribute::class];

        parent::__construct($factoryElement, $factoryCollection, $escaper, [
            'legend' => __('Additional'),
        ]);
    }

    public function getBasicChildrenHtml(): string
    {
        if (in_array($this->attribute->getFrontendInput(), ['select', 'multiselect'])) {
            $this->addField(AttributeConfigInterface::OPTIONS_SORT_BY, 'select', [
                'name'   => AttributeConfigInterface::OPTIONS_SORT_BY,
                'label'  => __('Sort Options by'),
                'values' => $this->attributeOptionSortBySource->toOptionArray(),
                'value'  => $this->attributeConfig->getOptionsSortBy(),
            ]);
        }

        return (string)parent::getBasicChildrenHtml();
    }
}
