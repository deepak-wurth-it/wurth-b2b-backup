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
 * @package   mirasvit/module-navigation
 * @version   2.0.12
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\AllProducts\Service;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Result\Page;
use Mirasvit\AllProducts\Config\Config;

class MetaService
{
    const DEFAULT_META = 'All Products';

    /**
     * @var Context
     */
    private $context;


    private $config;

    public function __construct(
        Config $config,
        Context $context
    ) {
        $this->config  = $config;
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return ($this->config->getTitle()) ? : $this->getDefaultMeta();
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaTitle()
    {
        return ($this->config->getMetaTitle()) ? : $this->getDefaultMeta();
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaDescription()
    {
        return ($this->config->getMetaDescription()) ? : $this->getDefaultMeta();
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultMeta()
    {
        return __(MetaService::DEFAULT_META);
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Page $page)
    {
        $pageConfig = $page->getConfig();
        $pageConfig->getTitle()->set((string)__($this->getMetaTitle()));
        $pageConfig->setMetadata('description', $this->getMetaDescription());
        $layout = $this->context->getLayout();
        if ($pageMainTitle = $layout->getBlock('page.main.title')) {
            $pageMainTitle->setPageTitle($this->getTitle());
        }

        return $page;
    }
}
