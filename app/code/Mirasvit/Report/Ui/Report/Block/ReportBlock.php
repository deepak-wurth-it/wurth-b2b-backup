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
 * @package   mirasvit/module-report
 * @version   1.3.112
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Report\Ui\Report\Block;

use Magento\Backend\Block\Template;

class ReportBlock extends Template
{
    const IS_DEBUG = false;

    /**
     * @var string
     */
    protected $_template = 'Mirasvit_Report::ui.phtml';

    public function __construct(
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getCssUrl()
    {
        if (self::IS_DEBUG) {
            return '';
        }

        return $this->getViewFileUrl('Mirasvit_Report::ui/app.min.css');
    }

    /**
     * @return string
     */
    public function getAppJsUrl()
    {
        if (self::IS_DEBUG) {
            return 'http://localhost:3000/app.js';
        }

        return $this->getViewFileUrl('Mirasvit_Report::ui/app.min.js');
    }

    /**
     * @return string
     */
    public function getVendorJsUrl()
    {
        if (self::IS_DEBUG) {
            return 'http://localhost:3000/vendor.js';
        }

        return $this->getViewFileUrl('Mirasvit_Report::ui/vendor.min.js');
    }
}
