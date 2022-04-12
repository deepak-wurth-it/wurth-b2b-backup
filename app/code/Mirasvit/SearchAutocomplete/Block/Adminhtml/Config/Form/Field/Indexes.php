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



namespace Mirasvit\SearchAutocomplete\Block\Adminhtml\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Mirasvit\SearchAutocomplete\Api\Data\IndexInterface;
use Mirasvit\SearchAutocomplete\Model\Index;
use Mirasvit\SearchAutocomplete\Model\IndexProvider;

/**
 * @method AbstractElement getElement()
 * @method $this setElement(AbstractElement $element)
 */
class Indexes extends Field
{
    private $indexProvider;

    public function __construct(
        IndexProvider $indexProvider,
        Context $context
    ) {
        $this->indexProvider = $indexProvider;

        return parent::__construct($context);
    }

    public function render(AbstractElement $element): string
    {
        $this->setElement($element);

        return $this->_toHtml();
    }

    /**
     * Available indexes
     */
    public function getIndexes(): array
    {
        return $this->indexProvider->getList();
    }

    public function getNamePrefix(Index $index): string
    {
        return $this->getElement()->getName() . '[' . $index->getIdentifier() . ']';
    }

    protected function _construct()
    {
        $this->setTemplate('Mirasvit_SearchAutocomplete::config/form/field/indexes.phtml');
    }
}
