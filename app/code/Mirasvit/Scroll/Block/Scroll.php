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

namespace Mirasvit\Scroll\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mirasvit\Scroll\Model\ConfigProvider;

class Scroll extends Template
{
    /**
     * @var ConfigProvider
     */
    private $config;

    /**
     * Scroll constructor.
     *
     * @param ConfigProvider $config
     * @param Context        $context
     * @param array          $data
     */
    public function __construct(ConfigProvider $config, Context $context, array $data = [])
    {
        $this->config = $config;

        parent::__construct($context, $data);
    }


    /**
     * Get pager block.
     * @return bool|\Magento\Theme\Block\Html\Pager
     */
    public function getPager()
    {
        return $this->getLayout()->getBlock('product_list_toolbar_pager');
    }

    /**
     * Get options for initializing scroll component.
     * @return array
     */
    public function getJsConfig()
    {
        $pager = $this->getPager();
        if (!$pager || !$pager->getCollection()) {
            return [];
        }

        $currentPage = (int)$pager->getCurrentPage();

        return [
            'mode'         => $this->config->getMode(),
            'pageNum'      => $currentPage,
            'initPageNum'  => $currentPage,
            'prevPageNum'  => $currentPage === 1 ? false : $currentPage - 1,
            'nextPageNum'  => $currentPage === (int)$pager->getLastPageNum() ? false : $currentPage + 1,
            'lastPageNum'  => $pager->getLastPageNum(),
            'loadPrevText' => $this->processText($this->config->getLoadPrevText()),
            'loadNextText' => $this->processText($this->config->getLoadNextText()),
        ];
    }

    /**
     * @return array
     */
    public function getInitConfig()
    {
        return [
            $this->config->getProductListSelector() => [
                'Mirasvit_Scroll/js/scroll' => $this->getJsConfig(),
            ],
        ];
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->config->isEnabled() && $this->config->getProductListSelector();
    }

    /**
     * @param string $text
     *
     * @return string
     */
    private function processText($text)
    {
        $pager = $this->getPager();

        $limit       = (int)$pager->getLimit();
        $size        = $this->getPager()->getCollection()->getSize();
        $currentPage = $this->getPager()->getCurrentPage();

        if (($currentPage + 1) * $limit > $size) {
            $limit = $size - $currentPage * $limit;
        }

        $text = (string)__($text);

        return str_replace('%limit%', $limit, $text);
    }
}
