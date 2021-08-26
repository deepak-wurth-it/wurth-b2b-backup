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



namespace Mirasvit\Core\Service;

use Magento\Framework\App\RequestInterface;
use Mirasvit\Core\Api\Service\ManualServiceInterface;
use Magento\Framework\Module\Dir\Reader as ModuleDirReader;
use Magento\Framework\Xml\Parser as XmlParser;
use Magento\Framework\Module\ModuleListInterface;

class ManualService implements ManualServiceInterface
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var ModuleDirReader
     */
    private $moduleDirReader;

    /**
     * @var XmlParser
     */
    private $parser;

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * ManualService constructor.
     * @param RequestInterface $request
     * @param ModuleDirReader $moduleDirReader
     * @param XmlParser $parser
     * @param ModuleListInterface $moduleList
     */
    public function __construct(
        RequestInterface $request,
        ModuleDirReader $moduleDirReader,
        XmlParser $parser,
        ModuleListInterface $moduleList
    ) {
        $this->request = $request;
        $this->moduleDirReader = $moduleDirReader;
        $this->parser = $parser;
        $this->moduleList = $moduleList;
    }

    /**
     * @return string|bool
     */
    public function getManualLink()
    {
        $manualLink = false;

        if ($this->request->isAjax()) {
            return false;
        }

        $data = $this->getManualData();

        if ($data) {
            $action = $this->request->getFullActionName();

            if ($action == 'adminhtml_system_config_edit') {
                $action = 'system_config_' . $this->request->getParam('section');
            }

            foreach ($data as $item) {
                if (!isset($item['_attribute'])) {
                    continue;
                }

                $item = $item['_attribute'];

                if ($action == $item['action']) {
                    $manualLink = $item;
                    $manualLink['title'] = (isset($item['title']) && $item['title'])
                        ? $item['title']
                        : self::DEFAULT_TITLE;
                    $manualLink['position'] = (isset($item['position']) && $item['position'])
                        ? $item['position']
                        : self::TOP_POSITION;
                    $manualLink['url'] = (strpos($manualLink['url'], 'http://') === false
                        && strpos($manualLink['url'], 'https://') === false)
                        ? self::DOCS_URL . ltrim($manualLink['url'], '/')
                        : $manualLink['url'];
                    break;
                }
            }

            if ($manualLink) {
                if (isset($manualLink['position-template'])
                    && $manualLink['position-template']
                ) {
                    $manualLink['template'] = $manualLink['position-template'];
                    return $manualLink;
                }

                switch ($manualLink['position']) {
                    case self::TOP_POSITION:
                        $manualLink['template'] = self::TOP_TEMPLATE;
                        break;
                    case self::BOTTOM_POSITION:
                        $manualLink['template'] = self::BOTTOM_TEMPLATE;
                        break;
                    case self::GRID_AFTER_POSITION:
                        $manualLink['template'] = self::GRID_AFTER_TEMPLATE;
                        break;
                    default:
                        $manualLink['template'] = self::TOP_TEMPLATE;
                        break;
                }
            }
        }

        return $manualLink;
    }

    /**
     * @return array
     */
    private function getManualData()
    {
        $xml = [];
        $module = $this->request->getControllerModule();

        if (strpos($module, 'Mirasvit_') !== false) {
            $xml = $this->loadXML($module);
        } elseif ($module == 'Magento_Config') {
            $xml = $this->getAllXML();
        }

        return $xml;
    }

    /**
     * @return array
     */
    private function getAllXML()
    {
        $help = [];
        $moduleList = $this->moduleList->getAll();
        if (defined('ARRAY_FILTER_USE_BOTH')) {
            $moduleListData = array_filter(array_keys($moduleList), function ($key) {
                return strpos($key, 'Mirasvit_') === 0;
            }, ARRAY_FILTER_USE_BOTH);
        } else {
            $moduleListData = array_filter($moduleList, function ($el) {
                return strpos($el['name'], 'Mirasvit_') === 0;
            });
        }

        $moduleListData = array_keys($moduleListData);
        if (($key = array_search('Mirasvit_Core', $moduleListData)) !== false) { //we don't check Mirasvit_Core helper
            unset($moduleListData[$key]);
        }

        foreach ($moduleListData as $module) {
            $xml = $this->loadXML($module);
            $help = array_merge($help, $xml);
        }

        return $help;
    }

    /**
     * @param string $module
     * @return array
     */
    private function loadXML($module)
    {
        $bp = $this->moduleDirReader->getModuleDir(self::MANUAL_FILE_PATH, $module);

        if ($bp == '/etc') {
            return [];
        }

        $filePath = $bp . '/' . self::MANUAL_FILE_NAME;

        if (file_exists($filePath)) {
            $xml = $this->parser->load($filePath)->xmlToArray();

            if (isset($xml['config'])) {
                return $xml['config']['_value']['link'];
            }
        }

        return [];
    }
}
