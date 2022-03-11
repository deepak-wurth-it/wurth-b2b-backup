<?php

namespace Wurth\Reportbug\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const EMAIL_TEMPLATE = 'report_bug_section/email/email_template';
    const EMAIL_SENDER = 'report_bug_section/email/sender';
    const ADMIN_EMAIL = 'report_bug_section/email/admin_email';
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Data constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Get admin email
     *
     * @return mixed
     */
    public function getAdminEmail()
    {
        return $this->getConfig(self::ADMIN_EMAIL);
    }

    /**
     * Get config
     *
     * @param string $path
     * @return mixed
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get email sender
     *
     * @return mixed
     */
    public function getEmailSender()
    {
        return $this->getConfig(self::EMAIL_SENDER);
    }

    /**
     * Get email template id
     *
     * @return mixed
     */
    public function getEmailTemplate()
    {
        return $this->getConfig(self::EMAIL_TEMPLATE);
    }
}
