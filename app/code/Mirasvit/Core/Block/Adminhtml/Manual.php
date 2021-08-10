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
 * @package   mirasvit/module-core
 * @version   1.2.122
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Core\Block\Adminhtml;

use Magento\Backend\Block\Template;

/**
 * @method $this setTitle($value)
 * @method string getTitle()
 *
 * @method $this setManualUrl($value)
 * @method string getManualUrl()
 *
 * @method $this setPosition($value)
 * @method string getPosition()
 */
class Manual extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Mirasvit_Core::manual.phtml';
}
