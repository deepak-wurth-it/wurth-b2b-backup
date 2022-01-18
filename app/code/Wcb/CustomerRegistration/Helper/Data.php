<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wcb\CustomerRegistration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        Context $context,
        EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
        $this->encryptor = $encryptor;
    }

    public function isExUserEnabled($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        return $this->scopeConfig->isSetFlag(
            'wcbreg/general/exenabled',
            $scope
        );
    }

    public function isNewUserEnabled($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        return $this->scopeConfig->isSetFlag(
            'wcbreg/general/newenabled',
            $scope
        );
    }

    public function getSiteKey($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        $secret = $this->scopeConfig->getValue(
            'wcbreg/general/sitekey',
            $scope
        );
        $secret = $this->encryptor->decrypt($secret);
        return $secret;
    }

    public function getSecretKey($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        $secret = $this->scopeConfig->getValue(
            'wcbreg/general/secretkey',
            $scope
        );
        $secret = $this->encryptor->decrypt($secret);        
        return $secret;
    }
}