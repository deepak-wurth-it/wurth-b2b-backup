<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wurth\Theme\Model\Order\Email;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\Template\TransportBuilderByStore;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Sales\Model\Order\Email\Container\Template;

class SenderBuilder extends \Magento\Sales\Model\Order\Email\SenderBuilder
{
    public function __construct(
        Template $templateContainer,
        IdentityInterface $identityContainer,
        TransportBuilder $transportBuilder,
        TransportBuilderByStore $transportBuilderByStore = null
    ) {
        parent::__construct(
            $templateContainer,
            $identityContainer,
            $transportBuilder,
            $transportBuilderByStore
        );
    }

    /**
     * Prepare and send email message
     *
     * @return void
     */
    public function send()
    {
        $this->configureEmailTemplate();
        $this->transportBuilder->addTo(
            $this->identityContainer->getCustomerEmail(),
            $this->identityContainer->getCustomerName()
        );

        $copyTo = $this->identityContainer->getEmailCopyTo();

        if (!empty($copyTo) && $this->identityContainer->getCopyMethod() == 'bcc') {
            foreach ($copyTo as $email) {
                $this->transportBuilder->addBcc($email);
            }
        }

        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
        if ($this->templateContainer->getTemplateId() === 'sales_email_order_template') {
            $this->sendCustomCopyTo();
        }
    }

    /**
     * @throws LocalizedException
     * @throws MailException
     */
    public function sendCustomCopyTo()
    {
        $copyTo = [];
        $vars = $this->templateContainer->getTemplateVars();
        if (isset($vars['order'])) {
            $order = $vars['order'];
            if ($order->getOrderConfirmationEmail()) {
                $confirmationEmail = $order->getOrderConfirmationEmail();
                $copyTo = explode(',', $confirmationEmail);
            }
        }

        if (!empty($copyTo)) {
            foreach ($copyTo as $email) {
                if ($email == $this->identityContainer->getCustomerEmail()) {
                    continue;
                }
                $this->configureEmailTemplate();
                $this->transportBuilder->addTo($email);
                $transport = $this->transportBuilder->getTransport();
                $transport->sendMessage();
            }
        }
    }
}
