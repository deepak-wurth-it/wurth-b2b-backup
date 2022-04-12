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



namespace Mirasvit\Search\Block\Index;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Service\IndexService;

/**
 * @method IndexInterface getIndex()
 */
class Base extends Template
{

    private $indexService;

    private $objectManager;

    public function __construct(
        IndexService $indexService,
        ObjectManagerInterface $objectManager,
        Context $context
    ) {
        $this->indexService  = $indexService;
        $this->objectManager = $objectManager;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function stripTags($data, $allowableTags = null, $allowHtmlEntities = false)
    {
        $data = preg_replace('/^\s*\/\/<!\[CDATA\[([\s\S]*)\/\/\]\]>\s*\z/', '$1', $data);
        $data = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $data);
        $data = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $data);
        $data = str_replace('>', '> ', $data); #adding space after tag <h1>..</h1><p>...</p>

        return parent::stripTags($data, $allowableTags, $allowHtmlEntities);
    }

    /**
     * Truncate text
     *
     * @param string $string
     *
     * @return string
     */
    public function truncate($string)
    {
        if (strlen($string) > 512) {
            $string = strip_tags($string);
            $string = substr($string, 0, 512) . '...';
        }

        return $string;
    }

    /**
     * @return ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * Return pager html for current collection.
     * @return string
     */
    public function getPager()
    {
        $pager = $this->getChildBlock('pager');

        if (!$pager) {
            return '';
        }

        if (!$pager->getCollection()) {
            $pager->setCollection($this->getCollection());
        }

        return $pager->toHtml();
    }

    /**
     * @return \Magento\Framework\Data\Collection
     */
    public function getCollection()
    {
        return $this->indexService->getSearchCollection($this->getIndex());
    }
}
