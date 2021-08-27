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



namespace Mirasvit\Core\Plugin;

use Magento\Framework\View\TemplateEngineFactory;
use Magento\Framework\View\TemplateEngineInterface;
use Mirasvit\Core\Api\Service\ManualServiceInterface;
use Mirasvit\Core\Service\Manual\AddInTemplateFactory;
use Magento\Framework\View\Element\BlockFactory;
use Magento\Framework\View\TemplateEngine\Php as TemplateEnginePhp;
use Magento\Framework\View\LayoutInterface;

class ManualLinkPlugin
{
    /**
     * @var ManualServiceInterface
     */
    private $manualService;

    /**
     * @var AddInTemplateFactory
     */
    private $addInTemplate;
    /**
     * @var TemplateEnginePhp
     */
    private $templateEnginePhp;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * ManualLinkPlugin constructor.
     * @param ManualServiceInterface $manualService
     * @param AddInTemplateFactory $addInTemplate
     * @param LayoutInterface $layout
     * @param TemplateEnginePhp $templateEnginePhp
     */
    public function __construct(
        ManualServiceInterface $manualService,
        AddInTemplateFactory $addInTemplate,
        LayoutInterface $layout,
        TemplateEnginePhp $templateEnginePhp
    ) {
        $this->manualService = $manualService;
        $this->layout = $layout;
        $this->addInTemplate = $addInTemplate;
        $this->templateEnginePhp = $templateEnginePhp;
    }

    /**
     * Add help block in template
     *
     * @param TemplateEngineFactory $subject
     * @param TemplateEngineInterface $invocationResult
     *
     * @return TemplateEngineInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreate($subject, TemplateEngineInterface $invocationResult)
    {
        $manualLink = $this->manualService->getManualLink();

        if (!$manualLink || !isset($manualLink['url']) || !isset($manualLink['template'])) {
            return $invocationResult;
        }

        $url = $manualLink['url'];
        $title = $manualLink['title'];
        $template = $manualLink['template'];
        $position = $manualLink['position'];

        /** @var \Mirasvit\Core\Block\Adminhtml\Manual $manualBlock */
        $manualBlock = $this->layout->createBlock('Mirasvit\Core\Block\Adminhtml\Manual');

        $manualBlock->setTitle($title)
            ->setManualUrl($url)
            ->setPosition($position);

        //we can't render block here using standard way
        $html = $this->templateEnginePhp->render($manualBlock, $manualBlock->getTemplateFile(), []);

        if ($html) {
            return $this->addInTemplate->create([
                'subject'       => $invocationResult,
                'template'      => $template,
                'helpBlockHtml' => $html,
            ]);
        }

        return $invocationResult;
    }
}
