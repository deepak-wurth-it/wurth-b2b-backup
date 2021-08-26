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

namespace Mirasvit\LayeredNavigation\Block\Adminhtml\Config\Form\Field;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Mirasvit\LayeredNavigation\Model\Config\Source\HorizontalFilterOptions;

class Attribute extends Select
{
    private $horizontalFilterOptions;

    public function __construct(
        HorizontalFilterOptions $horizontalFilterOptions,
        Context $context,
        array $data = []
    ) {
        $this->horizontalFilterOptions = $horizontalFilterOptions;

        parent::__construct($context, $data);
    }

    public function setInputName(string $value): self
    {
        return $this->setData('name', $value);
    }

    public function _toHtml(): string
    {
        foreach ($this->horizontalFilterOptions->toOptionArray() as $option) {
            $this->addOption($option['value'], $option['label']);
        }

        return parent::_toHtml();
    }
}
