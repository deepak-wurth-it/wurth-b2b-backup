<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


namespace Amasty\Base\Observer;

use Amasty\Base\Model\Feed\NewsProcessor;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class PreDispatchAdminActionController implements ObserverInterface
{
    /**
     * @var Session
     */
    private $backendSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var NewsProcessor
     */
    private $newsProcessor;

    public function __construct(
        NewsProcessor $newsProcessor,
        Session $backendAuthSession,
        LoggerInterface $logger
    ) {
        $this->backendSession = $backendAuthSession;
        $this->logger = $logger;
        $this->newsProcessor = $newsProcessor;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->backendSession->isLoggedIn()) {
            try {
                $this->newsProcessor->checkUpdate();
                $this->newsProcessor->removeExpiredItems();
            } catch (\Exception $exception) {
                $this->logger->critical($exception);
            }
        }
    }
}
