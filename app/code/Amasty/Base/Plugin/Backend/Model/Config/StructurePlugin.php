<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


namespace Amasty\Base\Plugin\Backend\Model\Config;

use Amasty\Base\Block\Adminhtml\System\Config\Advertise;
use Amasty\Base\Model\Feed\AdsProvider;
use Amasty\Base\Model\Config;
use Amasty\Base\Model\LinkValidator;
use Magento\Config\Model\Config\ScopeDefiner;
use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Element\Section;

/**
 * Class StructurePlugin add advertising modules
 */
class StructurePlugin
{
    /**
     * Fieldset for each tab in configuration us modules
     */
    const AMASTY_ADVERTISE = [
        'amasty_advertise' => [
                'id' => 'amasty_base_advertise',
                'type' => 'text',
                'label' => 'Amasty Base Advertise',
                'children' => [
                    'label' => [
                        'id' => 'label',
                        'label' => '',
                        'type' => 'label',
                        'showInDefault' => '1',
                        'showInWebsite' => '1',
                        'showInStore' => '1',
                        'comment' => '',
                        'frontend_model' => Advertise::class,
                        '_elementType' => 'field'
                    ]
                ],
                'showInDefault' => '1',
                'showInWebsite' => '1',
                'showInStore' => '1'
            ]
    ];

    /**
     * Tab name
     */
    const AMASTY_TAB_NAME = 'amasty';

    /**
     * @var AdsProvider
     */
    private $adsProvider;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ScopeDefiner
     */
    private $scopeDefiner;

    /**
     * @var LinkValidator
     */
    private $linkValidator;

    public function __construct(
        AdsProvider $adsProvider,
        Config $config,
        ScopeDefiner $scopeDefiner,
        LinkValidator $linkValidator
    ) {
        $this->adsProvider = $adsProvider;
        $this->config = $config;
        $this->scopeDefiner = $scopeDefiner;
        $this->linkValidator = $linkValidator;
    }

    /**
     * @param Structure $subject
     * @param Section $result
     *
     * @return Section
     */
    public function afterGetElementByPathParts(
        Structure $subject,
        $result
    ) {
        if (!$this->config->isAdsEnabled()) {
            return $result;
        }

        $displayedAdvertise = [];
        $moduleSection = $result->getData();

        if (isset($moduleSection['tab']) && $moduleSection['tab'] === self::AMASTY_TAB_NAME) {
            $moduleChilds = $moduleSection['children'];
        } else {
            return $result;
        }

        if ($moduleChilds) {
            if (isset($moduleSection['resource'])) {
                $moduleCode = strtok($moduleSection['resource'], '::');
                $displayedAdvertise = $this->adsProvider->getDisplayAdvertise($moduleCode);
                if ($displayedAdvertise) {
                    $advertiseSection = $this->getSectionAdvertise($displayedAdvertise);
                }
            }
        }

        if ($displayedAdvertise) {
            if (!empty($advertiseSection)) {
                $moduleSection['children'] = array_merge($moduleChilds, $advertiseSection);
            }
            $result->setData($moduleSection, $this->scopeDefiner->getScope());
        }

        return $result;
    }

    /**
     * @param array $displayedAdvertise
     *
     * @return array
     */
    private function getSectionAdvertise($displayedAdvertise)
    {
        $advertiseSection = self::AMASTY_ADVERTISE;

        if (isset($displayedAdvertise['text'])) {
            $displayedAdvertise['text'] = $this->validateComment($displayedAdvertise['text']);
        } else {
            return [];
        }

        $advertiseSection['amasty_advertise']['data'] = $displayedAdvertise;

        if (isset($displayedAdvertise['tab_name'])) {
            $advertiseSection['amasty_advertise']['label'] = strip_tags($displayedAdvertise['tab_name']);

            return $advertiseSection;
        }

        return [];
    }

    /**
     * @param string $textField
     *
     * @return string
     */
    private function validateComment($textField)
    {
        if (!empty($textField)) {
            preg_match('/\[(.*)\]/', $textField, $explodedArray);

            if (isset($explodedArray[0], $explodedArray[1])) {
                $linkArray = explode('|', $explodedArray[1]);

                if (isset($linkArray[0], $linkArray[1]) && $this->linkValidator->validate($linkArray[0])) {
                    $format = '<a href="%s"' . strip_tags('title="%s"') . '>%s</a>';
                    $link = sprintf($format, $linkArray[0], $linkArray[1], $linkArray[1]);
                    $text = strip_tags(str_replace($explodedArray[0], $link, $textField), '<a>');

                    return $text;
                }
            }

            return strip_tags($textField);
        }

        return '';
    }
}
