<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


namespace Amasty\Base\Block\Adminhtml;

use Amasty\Base\Model\ModuleListProcessor;
use Magento\Backend\Block\Template;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Store\Model\ScopeInterface;
use Amasty\Base\Model\Source\NotificationType;
use Amasty\Base\Model\Config;

class Notification extends Field
{
    protected $_template = 'Amasty_Base::notification.phtml';

    /**
     * @var ModuleListProcessor
     */
    private $moduleListProcessor;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        Template\Context $context,
        ModuleListProcessor $moduleListProcessor,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleListProcessor = $moduleListProcessor;
        $this->config = $config;
    }

    protected function _toHtml()
    {
        if ($this->isSetNotification()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * @return int|null
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getUpdatesCount()
    {
        $modules = $this->moduleListProcessor->getModuleList();

        return count($modules['hasUpdate']);
    }

    /**
     * @return bool
     */
    protected function isSetNotification()
    {
        $value = $this->config->getEnabledNotificationTypes();

        return in_array(NotificationType::AVAILABLE_UPDATE, $value);
    }
}
