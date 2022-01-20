<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/

declare(strict_types=1);

namespace Amasty\Base\Plugin\Backend\Model\Config;

use Amasty\Base\Block\Adminhtml\System\Config\Information;
use Magento\Config\Model\Config\ScopeDefiner;
use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\StructureElementInterface;

class AddInformationBlockPlugin
{
    /**
     * @var ScopeDefiner
     */
    private $scopeDefiner;

    public function __construct(
        ScopeDefiner $scopeDefiner
    ) {
        $this->scopeDefiner = $scopeDefiner;
    }

    public function afterGetElementByPathParts(
        Structure $subject,
        StructureElementInterface $result
    ): StructureElementInterface {
        $moduleSection = $result->getData();

        if (!isset($moduleSection['tab'])
            || $moduleSection['tab'] !== StructurePlugin::AMASTY_TAB_NAME
            || !isset($moduleSection['resource'])
        ) {
            return $result;
        }
        $moduleChildes = &$moduleSection['children'];
        if (isset($moduleChildes['amasty_information'])) {
            return $result; //backward compatibility
        }
        $moduleCode = strtok($moduleSection['resource'], '::');
        $moduleChildes =
            [
                'amasty_information' => [
                    'id' => 'amasty_information',
                    'translate' => 'label',
                    'type' => 'text',
                    'sortOrder' => '1',
                    'showInDefault' => '1',
                    'showInWebsite' => '1',
                    'showInStore' => '1',
                    'label' => 'Information',
                    'frontend_model' => Information::class,
                    '_elementType' => 'group',
                    'path' => $moduleSection['id'] ?? '',
                    'module_code' => $moduleCode
                ]
            ] + $moduleChildes;
        $result->setData($moduleSection, $this->scopeDefiner->getScope());

        return $result;
    }
}
