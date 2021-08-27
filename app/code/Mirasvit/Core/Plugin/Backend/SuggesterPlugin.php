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



namespace Mirasvit\Core\Plugin\Backend;

use Magento\Config\Model\Config\ScopeDefiner;
use Magento\Config\Model\Config\Structure;
use Mirasvit\Core\Block\Adminhtml\Config\SuggesterField;
use Mirasvit\Core\Service\CompatibilityService;
use Mirasvit\Core\Service\SuggesterService;

/**
 * @see \Magento\Config\Model\Config\Structure::getElementByPathParts()
 */
class SuggesterPlugin
{
    private $scopeDefiner;

    private $suggesterService;

    public function __construct(
        ScopeDefiner $scopeDefiner,
        SuggesterService $suggesterService
    ) {
        $this->scopeDefiner     = $scopeDefiner;
        $this->suggesterService = $suggesterService;
    }

    /**
     * @param Structure                                              $subject
     * @param \Magento\Config\Model\Config\Structure\Element\Section $result
     *
     * @return Structure\Element\Section
     */
    public function afterGetElementByPathParts(Structure $subject, $result)
    {
        if (CompatibilityService::isMarketplace()) {
            return $result;
        }

        //check if enabled
        $sectionData = $result->getData();

        if (!isset($sectionData['tab']) || $sectionData['tab'] !== 'mirasvit') {
            return $result;
        }

        list($moduleName) = explode('::', $sectionData['resource']);

        if (!$moduleName) {
            return $result;
        }

        $suggestion = $this->suggesterService->getSuggestion($moduleName);

        if (!$suggestion) {
            return $result;
        }

        $sectionData['children']['suggester'] = [
            'id'            => 'suggester',
            'type'          => 'text',
            'sortOrder'     => '100000',
            'showInDefault' => '1',
            'showInWebsite' => '1',
            'showInStore'   => '1',
            'label'         => $suggestion['label'],
            'children'      => [
                'label' => [
                    'id'             => 'label',
                    'label'          => 'Status',
                    'type'           => 'label',
                    'frontend_model' => SuggesterField::class,
                    'showInDefault'  => '1',
                    'showInWebsite'  => '1',
                    'showInStore'    => '1',
                    'comment'        => $suggestion['text'],
                    '_elementType'   => 'field',
                ],
            ],
        ];


        $result->setData($sectionData, $this->scopeDefiner->getScope());

        return $result;
    }
}
