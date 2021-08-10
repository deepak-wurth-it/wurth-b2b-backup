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



namespace Mirasvit\Core\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Mirasvit\Core\Model\LicenseFactory;

class OnLayoutRenderElementObserver implements ObserverInterface
{
    /**
     * @var LicenseFactory
     */
    protected $licenseFactory;

    /**
     * @param LicenseFactory $licenseFactory
     */
    public function __construct(
        LicenseFactory $licenseFactory
    ) {
        $this->licenseFactory = $licenseFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(EventObserver $observer)
    {
        $event = $observer->getEvent();
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $event->getData('layout');
        $name = $event->getData('element_name');

        if ($name) {
            /** @var \Magento\Framework\View\Element\AbstractBlock $block */
            $block = $layout->getBlock($name);
            if (is_object($block) && substr(get_class($block), 0, 9) == 'Mirasvit\\') {
                if ($block instanceof \Mirasvit\Core\Block\Adminhtml\Menu) {
                    return;
                }

                $status = $this->licenseFactory->create()->getStatus(get_class($block));

                if ($status === true) {
                    return;
                }

                $transport = $event->getData('transport');

                if (!OnActionPredispatchObserver::$notified) {
                    $transport->setData('output', "<div class='message message-warning warning'>$status</div>");
                    OnActionPredispatchObserver::$notified = true;
                } else {
                    $transport->setData('output', "");
                }
            }
        }
    }
}
