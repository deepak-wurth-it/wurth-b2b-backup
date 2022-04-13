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


namespace Mirasvit\SearchSphinx\Block\Adminhtml\Config\Form\Field\Command;

use Mirasvit\SearchSphinx\Block\Adminhtml\Config\Form\Field\Command;
use Mirasvit\Core\Service\CompatibilityService;

class Restart extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        if (! CompatibilityService::isMarketplace()) {
            $this->setTemplate('Mirasvit_Search::config/form/field/command.phtml');
        }
        return parent::_prepareLayout();
    }

    /**
     * {@inheritdoc}
     */
    public function getAction()
    {
        return 'restart';
    }
}
