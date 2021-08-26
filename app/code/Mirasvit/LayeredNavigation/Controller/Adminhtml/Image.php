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

namespace Mirasvit\LayeredNavigation\Controller\Adminhtml;

use Magento\Backend\App\Action;

abstract class Image extends Action
{
    /**
     * @var Action\Context
     */
    private $context;

    /**
     * @var \Magento\Backend\Model\Session
     */
    private $backendSession;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @var \Mirasvit\Core\Helper\Cron
     */
    private $cronHelper;

    public function __construct(
        \Mirasvit\Core\Helper\Cron $cronHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\Helper\Data $jsonEncoder,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->cronHelper     = $cronHelper;
        $this->date           = $date;
        $this->registry       = $registry;
        $this->jsonEncoder    = $jsonEncoder;
        $this->context        = $context;
        $this->backendSession = $context->getSession();
        $this->resultFactory  = $context->getResultFactory();

        parent::__construct($context);
    }

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->_setActiveMenu('Magento_Catalog::attributes_attributes');
        $this->_view->getLayout()->getBlock('head');

        return $this;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Magento_Catalog::attributes_attributes');
    }
}
