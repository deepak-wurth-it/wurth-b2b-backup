<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/

declare(strict_types=1);

namespace Amasty\Base\Block\Adminhtml\System\Config\InformationBlocks;

use Amasty\Base\Model\Feed\ExtensionsProvider;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\Element\Template;

class ExistingConflicts extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_Base::config/information/existing_conflicts.phtml';

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var ExtensionsProvider
     */
    private $extensionsProvider;

    public function __construct(
        Template\Context $context,
        Manager $moduleManager,
        ExtensionsProvider $extensionsProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleManager = $moduleManager;
        $this->extensionsProvider = $extensionsProvider;
    }

    public function getElement(): AbstractElement
    {
        return $this->getParentBlock()->getElement();
    }

    public function getConflictsMessages(): array
    {
        $messages = [];

        foreach ($this->getExistingConflicts() as $moduleName) {
            if ($this->moduleManager->isEnabled($moduleName)) {
                $messages[] = __(
                    'Incompatibility with the %1. '
                    . 'To avoid the conflicts we strongly recommend turning off '
                    . 'the 3rd party mod via the following command: "%2"',
                    $moduleName,
                    'magento module:disable ' . $moduleName
                );
            }
        }

        return $messages;
    }

    private function getExistingConflicts(): array
    {
        $conflicts = [];
        $moduleCode = $this->getElement()->getDataByPath('group/module_code');
        $module = $this->extensionsProvider->getFeedModuleData($moduleCode);
        if ($module && isset($module['conflictExtensions'])) {
            array_map(function ($extension) use (&$conflicts) {
                $conflicts[] = trim($extension);
            }, explode(',', $module['conflictExtensions']));
        }

        return $conflicts;
    }
}
