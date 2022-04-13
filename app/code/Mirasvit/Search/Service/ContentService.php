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

use Magento\Cms\Model\Template\FilterProvider as CmsFilterProvider;
use Magento\Email\Model\TemplateFactory as EmailTemplateFactory;
use Magento\Framework\App\State as AppState;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Mirasvit\Core\Service\SerializeService;
use Magento\Framework\App\ObjectManager;

class ContentService
{
    private $emulation;

    private $filterProvider;

    private $templateFactory;

    private $appState;

    public function __construct(
        AppEmulation $emulation,
        CmsFilterProvider $filterProvider,
        EmailTemplateFactory $templateFactory,
        AppState $appState
    ) {
        $this->emulation       = $emulation;
        $this->filterProvider  = $filterProvider;
        $this->templateFactory = $templateFactory;
        $this->appState        = $appState;
    }

    public function processHtmlContent(int $storeId, string $html): string
    {
        if (strripos($html, 'mgz_pagebuilder') !== false) {
            $json = str_ireplace(['[mgz_pagebuilder]', '[/mgz_pagebuilder]'], '', $html);
            $contentArray = SerializeService::decode($json);
            $content = [];

            array_walk_recursive($contentArray, function ($item, $key) use (&$content) {
                if ($key == 'content') {
                    $content[] = strip_tags($item);
                }
            });

            $html = implode(' ', $content);
        }

        $html = $this->cleanHtml($html);
        $this->emulation->stopEnvironmentEmulation();

        try {
            $this->emulation->startEnvironmentEmulation($storeId, 'frontend', true);
            $template = $this->templateFactory->create();
            $template->emulateDesign($storeId);
            $template->setTemplateText($html)
                ->setIsPlain(false);
            $template->setTemplateFilter($this->filterProvider->getPageFilter());
            $this->emulation->stopEnvironmentEmulation();
            $html = $template->getProcessedTemplate([]);
        } catch (\Exception $e) {
            $state = ObjectManager::getInstance()->get('\Magento\Framework\App\State');
            $state->emulateAreaCode(
                'frontend',
                function (&$html, $storeId) {
                    $template = $this->templateFactory->create();
                    $template->emulateDesign($storeId);
                    $template->setTemplateText($html)
                        ->setIsPlain(false);
                    $template->setTemplateFilter($this->filterProvider->getPageFilter());
                    $html = $template->getProcessedTemplate([]);
                },
                [&$html, $storeId]
            );
        } finally {
            $this->emulation->stopEnvironmentEmulation();
        }

        return (string)$html;
    }

    private function cleanHtml(string $html): string
    {
        $re = '/(mgz_pagebuilder.*mgz_pagebuilder)*/m';
        preg_match_all($re, $html, $matches, PREG_SET_ORDER, 0);
        foreach ($matches as $match) {
            $html = str_replace($match[0], "", $html);
        }

        return $html;
    }
}
