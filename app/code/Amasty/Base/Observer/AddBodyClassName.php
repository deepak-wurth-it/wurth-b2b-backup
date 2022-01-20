<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


namespace Amasty\Base\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class AddBodyClassName
 * frontend area, layout_render_before event
 */
class AddBodyClassName implements ObserverInterface
{
    const SMARTWAVE_PORTO_CODE = 'Smartwave/porto';

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    private $pageConfig;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    private $design;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \Magento\Framework\View\Page\Config $pageConfig,
        \Magento\Framework\View\DesignInterface $design,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->pageConfig = $pageConfig;
        $this->design = $design;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (strpos($this->design->getDesignTheme()->getCode(), self::SMARTWAVE_PORTO_CODE) !== false) {
            try {
                if ($this->pageConfig->getElementAttribute(
                    \Magento\Framework\View\Page\Config::ELEMENT_TYPE_BODY,
                    \Magento\Framework\View\Page\Config::BODY_ATTRIBUTE_CLASS
                )) {
                    $this->pageConfig->addBodyClass('am-porto-cmtb');
                }
            } catch (\Exception $exception) {
                $this->logger->critical($exception);
            }
        }
    }
}
