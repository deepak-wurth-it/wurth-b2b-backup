<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


namespace Amasty\Base\Controller\Adminhtml\Notification;

use Amasty\Base\Model\Config;
use Magento\Backend\App\Action;

class Frequency extends \Magento\Backend\App\Action
{
    /**
     * @var \Amasty\Base\Model\Config
     */
    private $config;

    /**
     * @var \Amasty\Base\Model\Source\Frequency
     */
    private $frequency;

    public function __construct(
        Action\Context $context,
        \Amasty\Base\Model\Config $config,
        \Amasty\Base\Model\Source\Frequency $frequency
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->frequency = $frequency;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $action = $this->getRequest()->getParam('action');

        switch ($action) {
            case 'less':
                $this->increaseFrequency();
                break;
            case 'more':
                $this->decreaseFrequency();
                break;
            default:
                $this->messageManager->addErrorMessage(
                    __(
                        'An error occurred while changing the frequency.'
                    )
                );
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Amasty_Base::config'
        );
    }

    protected function decreaseFrequency()
    {
        $currentValue = $this->config->getCurrentFrequencyValue();
        $allValues = $this->frequency->toOptionArray();
        $resultValue = null;
        foreach ($allValues as $option) {
            if ($option['value'] != $currentValue) {
                $resultValue = $option['value'];
            } else {
                if ($resultValue) {
                    $this->config->changeFrequency($resultValue);
                }

                break;
            }
        }

        $this->messageManager->addSuccessMessage(
            __(
                'You will get more messages of this type. Notification frequency has been updated.'
            )
        );
    }

    protected function increaseFrequency()
    {
        $currentValue = $this->config->getCurrentFrequencyValue();
        $allValues = $this->frequency->toOptionArray();
        $resultValue = null;
        foreach ($allValues as $option) {
            if ($option['value'] == $currentValue) {
                $resultValue = $option['value'];
            }

            if ($resultValue && $option['value'] != $resultValue) {
                $this->config->changeFrequency($option['value']);//save next option
                break;
            }
        }

        $this->messageManager->addSuccessMessage(
            __(
                'You will get less messages of this type. Notification frequency has been updated.'
            )
        );
    }
}
