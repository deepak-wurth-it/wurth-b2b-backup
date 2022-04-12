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
 * @package   mirasvit/module-search-ultimate
 * @version   2.0.56
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Service;

use Magento\Framework\Module\Manager;
use Magento\Framework\Module\ModuleListInterface;
use Mirasvit\Core\Service\AbstractValidator;
use Mirasvit\Search\Model\ConfigProvider;

class ValidationService extends AbstractValidator
{
    private $moduleManager;

    private $moduleList;

    private $config;

    public function __construct(
        Manager $moduleManager,
        ModuleListInterface $moduleList,
        ConfigProvider $config
    ) {
        $this->moduleManager = $moduleManager;
        $this->moduleList    = $moduleList;
        $this->config        = $config;
    }

    public function testKnownConflicts(): void
    {
        $known = ['Mageworks_SearchSuite', 'Magento_Solr', 'Magento_ElasticSearch'];

        foreach ($known as $moduleName) {
            if ($this->moduleManager->isEnabled($moduleName)) {
                $this->addError('Please disable {0} module.', [$moduleName]);
            }
        }
    }

    public function testPossibleConflicts(): void
    {
        $exceptions = ['Magento_Search', 'Magento_CatalogSearch'];

        foreach ($this->moduleList->getAll() as $module) {
            $moduleName = $module['name'];

            if (in_array($moduleName, $exceptions)) {
                continue;
            }

            if (stripos($moduleName, 'mirasvit') !== false) {
                continue;
            }

            if (stripos($moduleName, 'magento') !== false) {
                continue;
            }

            if (stripos($moduleName, 'search') !== false && $this->moduleManager->isEnabled($moduleName)) {
                $this->addWarning("Possible conflict with {0} module.", [$moduleName]);
            }
        }
    }
}
